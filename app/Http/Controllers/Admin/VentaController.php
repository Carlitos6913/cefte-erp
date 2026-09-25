<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ProductVariant;
use App\Models\Venta;
use App\Services\NumeradorService;
use App\Services\SalidaVentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use RuntimeException;

class VentaController extends Controller
{
    protected NumeradorService $numerador;

    protected SalidaVentaService $salidaVenta;

    public function __construct(
        NumeradorService $numerador,
        SalidaVentaService $salidaVenta,
    ) {
        $this->numerador = $numerador;
        $this->salidaVenta = $salidaVenta;
    }

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
        $saldos = $this->salidaVenta->saldosPorVariante();

        $productos = ProductVariant::where('is_active', true)
            ->with('unitMeasure:id,name,symbol')
            ->with('product:id,name,code')
            ->whereIn('id', array_keys($saldos))
            ->orderBy('sku')
            ->get([
                'id',
                'product_id',
                'unit_measure_id',
                'sku',
                'unit_price',
                'stock',
            ])
            ->map(function ($producto) use ($saldos) {
                $producto->saldo_autorizado = (int) ($saldos[$producto->id] ?? 0);

                return $producto;
            });

        return Inertia::render('Ventas/Create', [
            'productos' => $productos,
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

        try {
            DB::transaction(function () use ($validated) {

                foreach ($validated['detalles'] as $detalle) {
                    $this->validarSaldo($detalle['product_variant_id'], $detalle['cantidad'], 0);
                }

                $numero = $this->numerador->siguiente('VEN');

                $total = collect($validated['detalles'])
                    ->sum(fn ($detalle) => $detalle['cantidad'] * $detalle['precio_unitario']);

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

                    $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];

                    $ventaDetalle = $venta->detalles()->create([
                        'product_variant_id' => $detalle['product_variant_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $subtotal,
                    ]);

                    // La salida ya movió el stock físico; la venta solo
                    // consume el saldo autorizado de despacho.
                    $this->salidaVenta->consumir(
                        $detalle['product_variant_id'],
                        $detalle['cantidad'],
                        $ventaDetalle->id,
                    );
                }
            });
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'detalles' => $e->getMessage(),
            ])->withInput();
        }

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

        $saldos = $this->salidaVenta->saldosPorVariante();

        $productos = ProductVariant::where('is_active', true)
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
            ])
            ->map(function ($producto) use ($saldos) {
                $producto->saldo_autorizado = (int) ($saldos[$producto->id] ?? 0);

                return $producto;
            });

        return Inertia::render('Ventas/Edit', [
            'venta' => $venta,
            'productos' => $productos,
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

        $total = collect($validated['detalles'])
            ->sum(fn ($detalle) => $detalle['cantidad'] * $detalle['precio_unitario']);

        $pagosRecibidos = $venta->pagos()->sum('monto');

        if (bccomp((string) $total, (string) $pagosRecibidos, 2) < 0) {
            return back()->withErrors([
                'total' => 'El nuevo total (Bs. '.number_format($total, 2).') no puede ser '
                    .'menor a lo ya pagado (Bs. '.number_format($pagosRecibidos, 2).').',
            ])->withInput();
        }

        if ($validated['pago_tipo'] === 'contado'
            && $pagosRecibidos > 0
            && bccomp((string) $pagosRecibidos, (string) $total, 2) < 0) {
            return back()->withErrors([
                'pago_tipo' => 'La venta tiene pagos registrados (Bs. '.number_format($pagosRecibidos, 2)
                    .') inferiores al total. Debe permanecer como crédito o completar el pago.',
            ])->withInput();
        }

        $saldoPendiente = $validated['pago_tipo'] === 'credito'
            ? max(0, $total - $pagosRecibidos)
            : 0;

        try {
            DB::transaction(function () use ($venta, $validated, $saldoPendiente, $total) {

                // El saldo consumido actualmente por esta venta se
                // devuelve al validar, así que se suma a lo disponible.
                foreach ($validated['detalles'] as $detalle) {
                    $consumidoPorVenta = (int) $venta->detalles
                        ->where('product_variant_id', $detalle['product_variant_id'])
                        ->sum('cantidad');

                    $this->validarSaldo(
                        $detalle['product_variant_id'],
                        $detalle['cantidad'],
                        $consumidoPorVenta,
                    );
                }

                // Devolver el saldo consumido por los detalles anteriores.
                $this->salidaVenta->liberarVenta($venta->id);
                $venta->detalles()->delete();

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

                    $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];

                    $ventaDetalle = $venta->detalles()->create([
                        'product_variant_id' => $detalle['product_variant_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $subtotal,
                    ]);

                    $this->salidaVenta->consumir(
                        $detalle['product_variant_id'],
                        $detalle['cantidad'],
                        $ventaDetalle->id,
                    );
                }
            });
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'detalles' => $e->getMessage(),
            ])->withInput();
        }

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

            // Devolver el saldo autorizado consumido; el stock físico
            // ya fue movido por la salida de despacho.
            $this->salidaVenta->liberarVenta($venta->id);

            $venta->update([
                'estado' => 'anulado',
                'saldo_pendiente' => 0,
            ]);
        });

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta anulada correctamente.');
    }

    private function validarSaldo(int $productVariantId, int $cantidad, int $consumidoPorVenta): void
    {
        $disponible = $this->salidaVenta->saldoDisponible($productVariantId) + $consumidoPorVenta;

        if ($cantidad > $disponible) {
            $variante = ProductVariant::with('product')->find($productVariantId);
            $nombre = $variante?->product?->name ?? $productVariantId;

            throw new RuntimeException(
                "Sin saldo autorizado suficiente para \"{$nombre}\". Autorizado disponible: {$disponible}."
            );
        }
    }
}
