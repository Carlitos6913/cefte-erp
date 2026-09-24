<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ProductVariant;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with([
            'usuario',
            'clienteRelacion',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->withCount('detalles')
            ->latest('fecha')
            ->latest('id')
            ->paginate(10);

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/Create', [
            'productos' => ProductVariant::where('is_active', true)
                ->with('unitMeasure:id,name,symbol')
                ->with('product:id,name,code')
                ->where('stock', '>', 0)
                ->orderBy('sku')
                ->get([
                    'id',
                    'product_id',
                    'unit_measure_id',
                    'sku',
                    'unit_price',
                    'stock',
                ]),
            'clientes' => Cliente::where('activo', true)
                ->orderBy('nombre')
                ->get([
                    'id',
                    'nombre',
                    'ci_nit',
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'cliente' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'pago_tipo' => ['required', 'string', 'in:contado,credito'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($validated['detalles'] as $detalle) {
            $variante = ProductVariant::findOrFail($detalle['product_variant_id']);

            if ($variante->stock < $detalle['cantidad']) {
                return back()->withErrors([
                    'detalles' => "Stock insuficiente para \"{$variante->product->name}\". Disponible: {$variante->stock}.",
                ])->withInput();
            }
        }

        DB::transaction(function () use ($validated) {

            $numero = 'VEN-'.now()->format('YmdHis');

            $total = collect($validated['detalles'])
                ->sum(function ($detalle) {
                    return $detalle['cantidad']
                        * $detalle['precio_unitario'];
                });

            $saldoPendiente = $validated['pago_tipo'] === 'credito'
                ? $total
                : 0;

            $venta = Venta::create([
                'numero' => $numero,
                'fecha' => $validated['fecha'],
                'cliente_id' => $validated['cliente_id'] ?? null,
                'cliente' => $validated['cliente'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => $total,
                'estado' => 'activo',
                'pago_tipo' => $validated['pago_tipo'],
                'saldo_pendiente' => $saldoPendiente,
                'user_id' => Auth::id(),
            ]);

            foreach ($validated['detalles'] as $detalle) {

                $subtotal =
                    $detalle['cantidad']
                    * $detalle['precio_unitario'];

                $venta->detalles()->create([
                    'product_variant_id' => $detalle['product_variant_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);

                ProductVariant::where('id', $detalle['product_variant_id'])
                    ->decrement('stock', $detalle['cantidad']);
            }
        });

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta registrada correctamente.');
    }

    public function show(string $id)
    {
        $venta = Venta::with([
            'usuario',
            'clienteRelacion',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
            'pagos.usuario',
        ])
            ->withSum('pagos', 'monto')
            ->findOrFail($id);

        return Inertia::render('Ventas/Show', [
            'venta' => $venta,
        ]);
    }

    public function edit(string $id)
    {
        $venta = Venta::with([
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->findOrFail($id);

        if ($venta->estado === 'anulado') {
            return redirect()
                ->route('ventas.show', $venta->id)
                ->with('error', 'No se puede editar una venta anulada.');
        }

        return Inertia::render('Ventas/Edit', [
            'venta' => $venta,
            'productos' => ProductVariant::where('is_active', true)
                ->with('unitMeasure:id,name,symbol')
                ->with('product:id,name,code')
                ->orderBy('sku')
                ->get([
                    'id',
                    'product_id',
                    'unit_measure_id',
                    'sku',
                    'unit_price',
                    'stock',
                ]),
            'clientes' => Cliente::where('activo', true)
                ->orderBy('nombre')
                ->get([
                    'id',
                    'nombre',
                    'ci_nit',
                ]),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $venta = Venta::with('detalles')
            ->findOrFail($id);

        if ($venta->estado === 'anulado') {
            return back()->with(
                'error',
                'No se puede modificar una venta anulada.'
            );
        }

        $validated = $request->validate([
            'fecha' => ['required', 'date'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'cliente' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'pago_tipo' => ['required', 'string', 'in:contado,credito'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($validated['detalles'] as $detalle) {
            $variante = ProductVariant::findOrFail($detalle['product_variant_id']);

            $stockActual = $variante->stock;

            $stockComprometido = $venta->detalles
                ->where('product_variant_id', $detalle['product_variant_id'])
                ->sum('cantidad');

            $stockDisponible = $stockActual + $stockComprometido;

            if ($stockDisponible < $detalle['cantidad']) {
                return back()->withErrors([
                    'detalles' => "Stock insuficiente para \"{$variante->product->name}\". Disponible: {$stockDisponible}.",
                ])->withInput();
            }
        }

        DB::transaction(function () use ($venta, $validated) {

            foreach ($venta->detalles as $detalle) {
                ProductVariant::where('id', $detalle->product_variant_id)
                    ->increment('stock', $detalle->cantidad);
            }

            $venta->detalles()->delete();

            $total = collect($validated['detalles'])
                ->sum(function ($detalle) {
                    return $detalle['cantidad']
                        * $detalle['precio_unitario'];
                });

            $pagosRecibidos = $venta->pagos()->sum('monto');

            $saldoPendiente = $validated['pago_tipo'] === 'credito'
                ? max(0, $total - $pagosRecibidos)
                : 0;

            $venta->update([
                'fecha' => $validated['fecha'],
                'cliente_id' => $validated['cliente_id'] ?? null,
                'cliente' => $validated['cliente'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => $total,
                'pago_tipo' => $validated['pago_tipo'],
                'saldo_pendiente' => $saldoPendiente,
            ]);

            foreach ($validated['detalles'] as $detalle) {

                $subtotal =
                    $detalle['cantidad']
                    * $detalle['precio_unitario'];

                $venta->detalles()->create([
                    'product_variant_id' => $detalle['product_variant_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);

                ProductVariant::where('id', $detalle['product_variant_id'])
                    ->decrement('stock', $detalle['cantidad']);
            }
        });

        return redirect()
            ->route('ventas.show', $venta->id)
            ->with('success', 'Venta actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $venta = Venta::with('detalles')
            ->findOrFail($id);

        if ($venta->estado === 'anulado') {
            return back()->with(
                'error',
                'La venta ya se encuentra anulada.'
            );
        }

        DB::transaction(function () use ($venta) {

            foreach ($venta->detalles as $detalle) {
                ProductVariant::where('id', $detalle->product_variant_id)
                    ->increment('stock', $detalle->cantidad);
            }

            $venta->update([
                'estado' => 'anulado',
                'saldo_pendiente' => 0,
            ]);
        });

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta anulada correctamente.');
    }
}
