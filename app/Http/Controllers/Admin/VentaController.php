<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ProductVariant;
use App\Models\Venta;
use App\Services\NumeradorService;
use App\Services\SalidaVentaService;
use App\Services\UnidadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;
use RuntimeException;

class VentaController extends Controller
{
    protected NumeradorService $numerador;

    protected SalidaVentaService $salidaVenta;

    protected UnidadService $unidades;

    public function __construct(
        NumeradorService $numerador,
        SalidaVentaService $salidaVenta,
        UnidadService $unidades,
    ) {
        $this->numerador = $numerador;
        $this->salidaVenta = $salidaVenta;
        $this->unidades = $unidades;
    }

    /**
     * Variantes vendibles con las unidades en las que se puede
     * capturar la cantidad (unidad base y, si tiene, caja).
     *
     * @return Collection<int, ProductVariant>
     */
    private function productosVendibles(?array $soloConSaldo = null): Collection
    {
        $consulta = ProductVariant::where('is_active', true)
            ->with(['unidadBase:id,name,symbol', 'unidadEmpaque:id,name,symbol'])
            ->with('unitMeasure:id,name,symbol')
            ->with('product:id,category_id,name,code,image_path')
            ->with('product.category:id,name');

        if ($soloConSaldo !== null) {
            $consulta->whereIn('id', $soloConSaldo);
        }

        return $consulta->conStock()
            ->orderBy('sku')
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
            ])
            ->each(function (ProductVariant $variante) {
                $variante->setAttribute(
                    'unidades',
                    $variante->unidadesCapturables()
                );
            });
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

        $resumen = [
            'total' => Venta::count(),
            'montoTotal' => (float) Venta::where('estado', 'activo')->sum('total'),
            'saldoPendiente' => (float) Venta::where('estado', 'activo')
                ->where('saldo_pendiente', '>', 0)
                ->sum('saldo_pendiente'),
            'credito' => Venta::where('pago_tipo', 'credito')->count(),
        ];

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'resumen' => $resumen,
        ]);
    }

    public function create()
    {
        $saldos = $this->salidaVenta->saldosPorVariante();

        $productos = $this->productosVendibles(array_keys($saldos))
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

    /**
     * Convierte cada línea a unidad base.
     *
     * El saldo autorizado y el consumo se comparan en unidades, así
     * que vender "2 cajas" descuenta 2 x factor_empaque del saldo.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     * @return array<int, array<string, mixed>>
     */
    private function normalizarDetalles(array $detalles): array
    {
        $factores = $this->unidades->factoresPorLinea($detalles);

        return array_map(function (array $detalle) use ($factores) {
            $factor = $factores[$detalle['product_variant_id']];

            $cantidadBase = (int) round($detalle['cantidad'] * $factor);
            $precioBase = round($detalle['precio_unitario'] / $factor, 2);

            return [
                'product_variant_id' => $detalle['product_variant_id'],
                'cantidad' => $cantidadBase,
                'unidad_id' => $detalle['unidad_id'],
                'cantidad_original' => $detalle['cantidad'],
                'precio_unitario' => $precioBase,
                'subtotal' => round($cantidadBase * $precioBase, 2),
            ];
        }, $detalles);
    }

    /**
     * Reglas de validación de las líneas de detalle de la venta.
     *
     * @return array<string, array<int, string>>
     */
    private function reglasDetalles(): array
    {
        return [
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'detalles.*.unidad_id' => ['required', 'integer', 'exists:unit_measures,id'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0.0001'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'fecha' => ['required', 'date'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'cliente' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'pago_tipo' => ['required', 'string', 'in:contado,credito'],
        ], $this->reglasDetalles()));

        try {
            $lineas = $this->normalizarDetalles($validated['detalles']);

            DB::transaction(function () use ($validated, $lineas) {

                foreach ($lineas as $linea) {
                    $this->validarSaldo($linea['product_variant_id'], $linea['cantidad'], 0);
                }

                $numero = $this->numerador->siguiente('VEN');

                $total = collect($lineas)->sum('subtotal');

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

                foreach ($lineas as $linea) {

                    $ventaDetalle = $venta->detalles()->create([
                        'product_variant_id' => $linea['product_variant_id'],
                        'cantidad' => $linea['cantidad'],
                        'unidad_id' => $linea['unidad_id'],
                        'cantidad_original' => $linea['cantidad_original'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'subtotal' => $linea['subtotal'],
                    ]);

                    // La salida ya movió el stock físico; la venta solo
                    // consume el saldo autorizado de despacho.
                    $this->salidaVenta->consumir(
                        $linea['product_variant_id'],
                        $linea['cantidad'],
                        $ventaDetalle->id,
                    );
                }
            });
        } catch (InvalidArgumentException|RuntimeException $e) {
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
            'detalles.unidad:id,name,symbol',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
            'detalles.productVariant.unidadBase:id,name,symbol',
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
            'detalles.unidad:id,name,symbol',
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

        $productos = $this->productosVendibles()
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

        $validated = $request->validate(array_merge([
            'fecha' => ['required', 'date'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'cliente' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'pago_tipo' => ['required', 'string', 'in:contado,credito'],
        ], $this->reglasDetalles()));

        try {
            $lineas = $this->normalizarDetalles($validated['detalles']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['detalles' => $e->getMessage()])->withInput();
        }

        $total = collect($lineas)->sum('subtotal');

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
            DB::transaction(function () use ($venta, $validated, $saldoPendiente, $total, $lineas) {

                // El saldo consumido actualmente por esta venta se
                // devuelve al validar, así que se suma a lo disponible.
                foreach ($lineas as $linea) {
                    $consumidoPorVenta = (int) $venta->detalles
                        ->where('product_variant_id', $linea['product_variant_id'])
                        ->sum('cantidad');

                    $this->validarSaldo(
                        $linea['product_variant_id'],
                        $linea['cantidad'],
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

                foreach ($lineas as $linea) {

                    $ventaDetalle = $venta->detalles()->create([
                        'product_variant_id' => $linea['product_variant_id'],
                        'cantidad' => $linea['cantidad'],
                        'unidad_id' => $linea['unidad_id'],
                        'cantidad_original' => $linea['cantidad_original'],
                        'precio_unitario' => $linea['precio_unitario'],
                        'subtotal' => $linea['subtotal'],
                    ]);

                    $this->salidaVenta->consumir(
                        $linea['product_variant_id'],
                        $linea['cantidad'],
                        $ventaDetalle->id,
                    );
                }
            });
        } catch (InvalidArgumentException|RuntimeException $e) {
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
            $variante = ProductVariant::with(['product', 'unitMeasure', 'unidadBase'])
                ->find($productVariantId);

            $nombre = $variante?->product?->name ?? $productVariantId;

            if ($variante?->presentacion) {
                $nombre .= " ({$variante->presentacion})";
            }

            $unidad = $variante?->unidadBase?->symbol ?? 'unidades';

            throw new RuntimeException(
                "Sin saldo autorizado suficiente para \"{$nombre}\". "
                ."Autorizado disponible: {$disponible} {$unidad}."
            );
        }
    }
}
