<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Salida;
use App\Services\InventarioService;
use App\Services\NumeradorService;
use App\Services\SalidaVentaService;
use Illuminate\Database\Eloquent\Collection;
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
            'detalles.lote:id,codigo,fecha_caducidad',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->withCount('detalles')
            ->latest('fecha')
            ->latest('id')
            ->paginate(10);

        $resumen = [
            'total' => Salida::count(),
            'esteMes' => Salida::where('fecha', '>=', now()->startOfMonth())->count(),
            'hoy' => Salida::whereDate('fecha', now()->toDateString())->count(),
            'anuladas' => Salida::where('estado', 'anulado')->count(),
        ];

        return Inertia::render('Inventario/Salidas/Index', [
            'salidas' => $salidas,
            'resumen' => $resumen,
        ]);
    }

    /**
     * Variantes que se pueden elegir en el selector jerárquico.
     *
     * En creación solo se ofrecen variantes con stock. En edición se
     * incluyen todas para poder mostrar las líneas que ya existían.
     *
     * @return Collection<int, ProductVariant>
     */
    private function productosParaSelector(bool $soloDisponibles): Collection
    {
        $consulta = ProductVariant::where('is_active', true)
            ->conStock()
            ->with([
                'unitMeasure:id,name,symbol',
                'unidadBase:id,name,symbol',
                'unidadEmpaque:id,name,symbol',
                'product:id,category_id,name,code,image_path',
                'product.category:id,name',
                'lotes' => fn ($query) => $query
                    ->where('stock', '>', 0)
                    ->orderBy('fecha_caducidad')
                    ->orderBy('id')
                    ->select('id', 'product_variant_id', 'codigo', 'fecha_caducidad', 'stock'),
            ])
            ->orderBy('sku');

        if ($soloDisponibles) {
            $consulta->conDisponible();
        }

        return $consulta
            ->get([
                'id',
                'product_id',
                'unit_measure_id',
                'unidad_base_id',
                'unidad_empaque_id',
                'factor_empaque',
                'sku',
                'flavor',
                'packaging_type',
                'net_content',
                'minimum_stock',
                'unit_price',
            ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Salidas/Create', [
            'productos' => $this->productosParaSelector(true),
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
            $variante = ProductVariant::with(['product', 'unitMeasure'])
                ->find($detalle['product_variant_id']);
            $disponible = $variante ? $this->inventario->stockDisponible($variante->id) : 0;

            if ($variante && $disponible < $detalle['cantidad']) {
                $etiqueta = $variante->presentacion
                    ? "{$variante->product?->name} ({$variante->presentacion})"
                    : $variante->product?->name;

                return back()->withErrors([
                    'detalles' => "Stock insuficiente para \"{$etiqueta}\". Disponible: {$disponible}.",
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

                // FEFO: primero sale el lote que caduca antes. El
                // consumo puede repartirse en varias líneas (una por
                // lote) manteniendo el mismo producto.
                $asignaciones = $this->inventario->consumirSalidaFefo(
                    $variante,
                    $detalle['cantidad'],
                    'salida',
                    $salida->id,
                    $numero,
                    Auth::id(),
                );

                foreach ($asignaciones as $asignacion) {
                    $subtotal = $asignacion['cantidad'] * $variante->unit_price;
                    $total += $subtotal;

                    $salida->detalles()->create([
                        'product_variant_id' => $variante->id,
                        'lote_id' => $asignacion['lote_id'],
                        'cantidad' => $asignacion['cantidad'],
                        'precio_unitario' => $variante->unit_price,
                        'subtotal' => $subtotal,
                    ]);
                }
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
            'detalles.lote:id,codigo,fecha_caducidad',
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
            'detalles.lote:id,codigo,fecha_caducidad',
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
            'productos' => $this->productosParaSelector(false),
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
                if ($detalle->lote_id === null) {
                    continue;
                }

                $this->inventario->revertirSalida(
                    $detalle->lote_id,
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

                $asignaciones = $this->inventario->consumirSalidaFefo(
                    $variante,
                    $detalle['cantidad'],
                    'salida',
                    $salida->id,
                    $salida->numero,
                    Auth::id(),
                );

                foreach ($asignaciones as $asignacion) {
                    $subtotal = $asignacion['cantidad'] * $variante->unit_price;
                    $total += $subtotal;

                    $salida->detalles()->create([
                        'product_variant_id' => $variante->id,
                        'lote_id' => $asignacion['lote_id'],
                        'cantidad' => $asignacion['cantidad'],
                        'precio_unitario' => $variante->unit_price,
                        'subtotal' => $subtotal,
                    ]);
                }
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
                if ($detalle->lote_id === null) {
                    continue;
                }

                $this->inventario->revertirSalida(
                    $detalle->lote_id,
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
