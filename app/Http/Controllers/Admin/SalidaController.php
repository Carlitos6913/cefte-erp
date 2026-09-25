<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Salida;
use App\Services\InventarioService;
use App\Services\NumeradorService;
use App\Services\SalidaVentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalidaController extends Controller
{
    protected InventarioService $inventario;

    protected NumeradorService $numerador;

    protected SalidaVentaService $salidaVenta;

    public function __construct(
        InventarioService $inventario,
        NumeradorService $numerador,
        SalidaVentaService $salidaVenta,
    ) {
        $this->inventario = $inventario;
        $this->numerador = $numerador;
        $this->salidaVenta = $salidaVenta;
    }

    public function index()
    {
        $salidas = Salida::with([
            'usuario',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->withCount('detalles')
            ->latest('fecha')
            ->latest('id')
            ->paginate(10);

        return Inertia::render('Inventario/Salidas/Index', [
            'salidas' => $salidas,
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Salidas/Create', [
            'productos' => ProductVariant::where('is_active', true)
                ->with('unitMeasure:id,name,symbol')
                ->with('product:id,name,code')
                ->where('stock', '>', 0)
                ->orderBy('sku')
                ->get(['id', 'product_id', 'unit_measure_id', 'sku', 'stock', 'unit_price']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'motivo' => ['required', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['detalles'] as $detalle) {
            $variante = ProductVariant::find($detalle['product_variant_id']);

            if ($variante && $variante->stock < $detalle['cantidad']) {
                return back()->withErrors([
                    'detalles' => "Stock insuficiente para \"{$variante->product->name}\". Disponible: {$variante->stock}.",
                ])->withInput();
            }
        }

        DB::transaction(function () use ($validated) {

            $numero = $this->numerador->siguiente('SAL');

            $total = 0;

            $salida = Salida::create([
                'numero' => $numero,
                'fecha' => $validated['fecha'],
                'motivo' => $validated['motivo'],
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => 0,
                'estado' => 'activo',
                'user_id' => Auth::id(),
            ]);

            foreach ($validated['detalles'] as $detalle) {

                $variante = ProductVariant::findOrFail($detalle['product_variant_id']);
                $subtotal = $detalle['cantidad'] * $variante->unit_price;
                $total += $subtotal;

                $salida->detalles()->create([
                    'product_variant_id' => $detalle['product_variant_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $variante->unit_price,
                    'subtotal' => $subtotal,
                ]);

                $this->inventario->registrarMovimiento(
                    $detalle['product_variant_id'],
                    'salida',
                    $detalle['cantidad'],
                    'salida',
                    $salida->id,
                    $numero,
                    Auth::id(),
                );
            }

            $salida->update(['total' => $total]);
        });

        return redirect()
            ->route('salidas.index')
            ->with('success', 'Salida registrada correctamente.');
    }

    public function show(string $id)
    {
        $salida = Salida::with([
            'usuario',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->findOrFail($id);

        return Inertia::render('Inventario/Salidas/Show', [
            'salida' => $salida,
        ]);
    }

    public function edit(string $id)
    {
        $salida = Salida::with([
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->findOrFail($id);

        if ($salida->estado === 'anulado') {
            return redirect()
                ->route('salidas.show', $salida->id)
                ->with('error', 'No se puede editar una salida anulada.');
        }

        if ($this->salidaVenta->salidaTieneVendidos($salida->id)) {
            return redirect()
                ->route('salidas.show', $salida->id)
                ->with('error', 'No se puede editar una salida con detalles ya vendidos. Anule la venta correspondiente primero.');
        }

        return Inertia::render('Inventario/Salidas/Edit', [
            'salida' => $salida,
            'productos' => ProductVariant::where('is_active', true)
                ->with('unitMeasure:id,name,symbol')
                ->with('product:id,name,code')
                ->orderBy('sku')
                ->get(['id', 'product_id', 'unit_measure_id', 'sku', 'stock', 'unit_price']),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $salida = Salida::with('detalles')->findOrFail($id);

        if ($salida->estado === 'anulado') {
            return back()->with('error', 'No se puede modificar una salida anulada.');
        }

        if ($this->salidaVenta->salidaTieneVendidos($salida->id)) {
            return back()->with(
                'error',
                'No se puede modificar una salida con detalles ya vendidos. Anule la venta correspondiente primero.'
            );
        }

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'motivo' => ['required', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($salida, $validated) {

            foreach ($salida->detalles as $detalle) {
                $this->inventario->revertir(
                    $detalle->product_variant_id,
                    $detalle->cantidad,
                    'salida',
                    $salida->id,
                    Auth::id(),
                );
            }

            $salida->detalles()->delete();

            $total = 0;

            foreach ($validated['detalles'] as $detalle) {
                $variante = ProductVariant::findOrFail($detalle['product_variant_id']);
                $subtotal = $detalle['cantidad'] * $variante->unit_price;
                $total += $subtotal;

                $salida->detalles()->create([
                    'product_variant_id' => $detalle['product_variant_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $variante->unit_price,
                    'subtotal' => $subtotal,
                ]);

                $this->inventario->registrarMovimiento(
                    $detalle['product_variant_id'],
                    'salida',
                    $detalle['cantidad'],
                    'salida',
                    $salida->id,
                    $salida->numero,
                    Auth::id(),
                );
            }

            $salida->update([
                'fecha' => $validated['fecha'],
                'motivo' => $validated['motivo'],
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => $total,
            ]);
        });

        return redirect()
            ->route('salidas.show', $salida->id)
            ->with('success', 'Salida actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $salida = Salida::with('detalles')->findOrFail($id);

        if ($salida->estado === 'anulado') {
            return back()->with('error', 'La salida ya se encuentra anulada.');
        }

        if ($this->salidaVenta->salidaTieneVendidos($salida->id)) {
            return back()->with(
                'error',
                'No se puede anular una salida con detalles ya vendidos. Anule la venta correspondiente primero.'
            );
        }

        DB::transaction(function () use ($salida) {

            foreach ($salida->detalles as $detalle) {
                $this->inventario->revertir(
                    $detalle->product_variant_id,
                    $detalle->cantidad,
                    'salida',
                    $salida->id,
                    Auth::id(),
                );
            }

            $salida->update(['estado' => 'anulado']);
        });

        return redirect()
            ->route('salidas.index')
            ->with('success', 'Salida anulada correctamente.');
    }
}
