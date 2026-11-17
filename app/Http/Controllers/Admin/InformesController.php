<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use App\Models\IngresoDetalle;
use App\Models\Lote;
use App\Models\Pago;
use App\Models\ProductVariant;
use App\Models\Salida;
use App\Models\SalidaDetalle;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InformesController extends Controller
{
    private const SECCIONES = ['resumen', 'ventas', 'inventario', 'ingresos', 'cobranzas'];

    public function index(Request $request)
    {
        $periodo = $this->resolverPeriodo($request);
        $seccion = $this->resolverSeccion($request);

        $inicio = Carbon::parse($periodo['inicio']);
        $fin = Carbon::parse($periodo['fin']);
        $inicioAnterior = Carbon::parse($periodo['inicio_anterior']);
        $finAnterior = Carbon::parse($periodo['fin_anterior']);

        $base = $this->datosBase($inicio, $fin, $inicioAnterior, $finAnterior);
        $inventario = $this->datosInventario($inicio, $fin);
        $ventas = $this->datosVentas($inicio, $fin);
        $ingresos = $this->datosIngresos($inicio, $fin);
        $cobranzas = $this->datosCobranzas($inicio, $fin);
        $tendencia = $this->construirTendencia($inicio, $fin, $periodo['granularidad']);

        return Inertia::render('Informes/Index', [
            'resumen' => [
                ...$base['resumen'],
                'inventario' => $inventario['resumen'],
            ],
            'comparativo' => $base['comparativo'],
            'ventas' => $ventas,
            'inventario' => $inventario,
            'ingresos' => $ingresos,
            'cobranzas' => $cobranzas,
            'tendencia' => $tendencia,
            'detalle' => $this->detalleSeccion($seccion, $inicio, $fin),
            'periodo' => $periodo,
            'filtros' => [
                'fecha_inicio' => $periodo['inicio'],
                'fecha_fin' => $periodo['fin'],
            ],
            'seccion' => $seccion,
            'esAdministrador' => $request->user()->hasRole('Administrador'),
        ]);
    }

    public function exportar(Request $request): StreamedResponse
    {
        $periodo = $this->resolverPeriodo($request);
        $seccion = $this->resolverSeccion($request);

        $inicio = Carbon::parse($periodo['inicio']);
        $fin = Carbon::parse($periodo['fin']);

        if ($seccion === 'inventario') {
            return $this->exportarInventario($periodo);
        }

        if ($seccion === 'resumen') {
            $inicioAnterior = Carbon::parse($periodo['inicio_anterior']);
            $finAnterior = Carbon::parse($periodo['fin_anterior']);
            $base = $this->datosBase($inicio, $fin, $inicioAnterior, $finAnterior);
            $inventario = $this->datosInventario($inicio, $fin);

            return $this->exportarResumen($base, $inventario['resumen'], $periodo);
        }

        if ($seccion === 'ventas') {
            return $this->exportarVentas($inicio, $fin, $periodo);
        }

        if ($seccion === 'ingresos') {
            return $this->exportarIngresos($inicio, $fin, $periodo);
        }

        return $this->exportarCobranzas($inicio, $fin, $periodo);
    }

    private function resolverPeriodo(Request $request): array
    {
        $hoy = Carbon::now()->startOfDay();
        $inicio = $this->fechaValida($request->input('fecha_inicio'), $hoy->copy()->startOfMonth());
        $fin = $this->fechaValida($request->input('fecha_fin'), $hoy);

        if ($inicio->gt($fin)) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        $dias = (int) $inicio->diffInDays($fin) + 1;
        $inicioAnterior = $inicio->copy()->subDays($dias);
        $finAnterior = $inicio->copy()->subDay();

        return [
            'inicio' => $inicio->toDateString(),
            'fin' => $fin->toDateString(),
            'inicio_anterior' => $inicioAnterior->toDateString(),
            'fin_anterior' => $finAnterior->toDateString(),
            'dias' => $dias,
            'granularidad' => $dias <= 45 ? 'dia' : 'mes',
        ];
    }

    private function fechaValida(mixed $valor, Carbon $alternativa): Carbon
    {
        if (! is_string($valor) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return $alternativa->copy()->startOfDay();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $valor)->startOfDay();
        } catch (\Throwable) {
            return $alternativa->copy()->startOfDay();
        }
    }

    private function resolverSeccion(Request $request): string
    {
        $seccion = $request->input('seccion', 'resumen');

        return in_array($seccion, self::SECCIONES, true) ? $seccion : 'resumen';
    }

    private function datosBase(Carbon $inicio, Carbon $fin, Carbon $inicioAnterior, Carbon $finAnterior): array
    {
        $desde = $inicio->toDateString();
        $hasta = $fin->toDateString();
        $desdeAnterior = $inicioAnterior->toDateString();
        $hastaAnterior = $finAnterior->toDateString();

        $ventasActivas = fn () => Venta::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$desde, $hasta]);

        $comprasActivas = fn () => Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$desde, $hasta]);

        $pagosPeriodo = fn () => Pago::query()->whereBetween('fecha', [$desde, $hasta]);

        $ventasAgregado = $ventasActivas()
            ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total')
            ->first();

        $comprasAgregado = $comprasActivas()
            ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total')
            ->first();

        $pagosAgregado = $pagosPeriodo()
            ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as total')
            ->first();

        $ventasPorTipo = $ventasActivas()
            ->selectRaw('pago_tipo, COUNT(*) as operaciones, COALESCE(SUM(total), 0) as monto')
            ->groupBy('pago_tipo')
            ->get()
            ->keyBy('pago_tipo');

        $ventasTotal = round((float) $ventasAgregado->total, 2);
        $ventasCantidad = (int) $ventasAgregado->cantidad;
        $comprasTotal = round((float) $comprasAgregado->total, 2);
        $cobradoTotal = round((float) $pagosAgregado->total, 2);

        $ventasAnterior = round((float) Venta::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$desdeAnterior, $hastaAnterior])
            ->sum('total'), 2);

        $comprasAnterior = round((float) Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$desdeAnterior, $hastaAnterior])
            ->sum('total'), 2);

        $cobradoAnterior = round((float) Pago::query()
            ->whereBetween('fecha', [$desdeAnterior, $hastaAnterior])
            ->sum('monto'), 2);

        $unidadesVendidas = round((float) VentaDetalle::query()
            ->whereHas('venta', fn ($query) => $query
                ->where('estado', 'activo')
                ->whereBetween('fecha', [$desde, $hasta]))
            ->sum('cantidad'), 2);

        $unidadesCompradas = round((float) IngresoDetalle::query()
            ->whereHas('ingreso', fn ($query) => $query
                ->where('estado', 'activo')
                ->whereBetween('fecha', [$desde, $hasta]))
            ->sum('cantidad'), 2);

        $cartera = fn () => Venta::query()
            ->where('estado', 'activo')
            ->where('pago_tipo', 'credito')
            ->where('saldo_pendiente', '>', 0);

        $porCobrar = round((float) $cartera()->sum('saldo_pendiente'), 2);
        $operacionesCredito = $cartera()->count();
        $clientesConDeuda = $cartera()->whereNotNull('cliente_id')->distinct()->count('cliente_id');

        return [
            'resumen' => [
                'ventas' => [
                    'total' => $ventasTotal,
                    'cantidad' => $ventasCantidad,
                    'ticket_promedio' => $ventasCantidad > 0 ? round($ventasTotal / $ventasCantidad, 2) : 0,
                    'unidades' => $unidadesVendidas,
                    'anuladas' => (int) Venta::query()
                        ->where('estado', 'anulado')
                        ->whereBetween('fecha', [$desde, $hasta])
                        ->count(),
                    'contado' => round((float) ($ventasPorTipo['contado']->monto ?? 0), 2),
                    'contado_operaciones' => (int) ($ventasPorTipo['contado']->operaciones ?? 0),
                    'credito' => round((float) ($ventasPorTipo['credito']->monto ?? 0), 2),
                    'credito_operaciones' => (int) ($ventasPorTipo['credito']->operaciones ?? 0),
                    'por_cobrar' => $porCobrar,
                    'credito_pendiente_operaciones' => $operacionesCredito,
                    'clientes_con_deuda' => $clientesConDeuda,
                ],
                'compras' => [
                    'total' => $comprasTotal,
                    'cantidad' => (int) $comprasAgregado->cantidad,
                    'unidades' => $unidadesCompradas,
                    'anuladas' => (int) Ingreso::query()
                        ->where('estado', 'anulado')
                        ->whereBetween('fecha', [$desde, $hasta])
                        ->count(),
                ],
                'cobros' => [
                    'total' => $cobradoTotal,
                    'cantidad' => (int) $pagosAgregado->cantidad,
                ],
            ],
            'comparativo' => [
                'ventas' => $this->comparativo($ventasTotal, $ventasAnterior),
                'compras' => $this->comparativo($comprasTotal, $comprasAnterior),
                'cobros' => $this->comparativo($cobradoTotal, $cobradoAnterior),
            ],
        ];
    }

    private function comparativo(float $actual, float $anterior): array
    {
        return [
            'actual' => $actual,
            'anterior' => $anterior,
            'variacion' => $anterior > 0
                ? round((($actual - $anterior) / $anterior) * 100, 1)
                : null,
        ];
    }

    private function datosVentas(Carbon $inicio, Carbon $fin): array
    {
        $desde = $inicio->toDateString();
        $hasta = $fin->toDateString();

        $topProductos = VentaDetalle::query()
            ->selectRaw('product_variant_id, SUM(cantidad) as cantidad, SUM(subtotal) as monto, COUNT(DISTINCT venta_id) as operaciones')
            ->whereHas('venta', fn ($query) => $query
                ->where('estado', 'activo')
                ->whereBetween('fecha', [$desde, $hasta]))
            ->with([
                'productVariant.product:id,name',
                'productVariant.unitMeasure:id,symbol',
                'productVariant.unidadBase:id,symbol',
            ])
            ->groupBy('product_variant_id')
            ->orderByDesc('monto')
            ->limit(10)
            ->get()
            ->map(fn (VentaDetalle $detalle) => [
                'id' => (int) $detalle->product_variant_id,
                'producto' => $detalle->productVariant?->product?->name ?? 'Producto eliminado',
                'presentacion' => $detalle->productVariant?->presentacion ?? '',
                'sku' => $detalle->productVariant?->sku ?? '',
                'unidad' => $detalle->productVariant?->unidadBase?->symbol
                    ?? $detalle->productVariant?->unitMeasure?->symbol
                    ?? 'und',
                'cantidad' => round((float) $detalle->cantidad, 2),
                'operaciones' => (int) $detalle->operaciones,
                'monto' => round((float) $detalle->monto, 2),
            ]);

        $topClientes = Venta::query()
            ->leftJoin('clientes', 'clientes.id', '=', 'ventas.cliente_id')
            ->where('ventas.estado', 'activo')
            ->whereBetween('ventas.fecha', [$desde, $hasta])
            ->selectRaw("COALESCE(clientes.nombre, ventas.cliente, 'Consumidor final') as cliente, COUNT(*) as operaciones, COALESCE(SUM(ventas.total), 0) as monto, COALESCE(SUM(ventas.saldo_pendiente), 0) as saldo")
            ->groupByRaw("COALESCE(clientes.nombre, ventas.cliente, 'Consumidor final')")
            ->orderByDesc('monto')
            ->limit(10)
            ->get()
            ->map(fn ($cliente) => [
                'cliente' => $cliente->cliente,
                'operaciones' => (int) $cliente->operaciones,
                'monto' => round((float) $cliente->monto, 2),
                'saldo' => round((float) $cliente->saldo, 2),
            ]);

        $categorias = VentaDetalle::query()
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('product_variants', 'product_variants.id', '=', 'venta_detalles.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('ventas.estado', 'activo')
            ->whereBetween('ventas.fecha', [$desde, $hasta])
            ->selectRaw("COALESCE(categories.name, 'Sin categoría') as categoria, SUM(venta_detalles.cantidad) as cantidad, SUM(venta_detalles.subtotal) as monto")
            ->groupByRaw("COALESCE(categories.name, 'Sin categoría')")
            ->orderByDesc('monto')
            ->get()
            ->map(fn ($categoria) => [
                'categoria' => $categoria->categoria,
                'cantidad' => round((float) $categoria->cantidad, 2),
                'monto' => round((float) $categoria->monto, 2),
            ]);

        $tiposPago = Venta::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('pago_tipo, COUNT(*) as operaciones, COALESCE(SUM(total), 0) as monto')
            ->groupBy('pago_tipo')
            ->orderByDesc('monto')
            ->get()
            ->map(fn ($tipo) => [
                'tipo' => $tipo->pago_tipo ?: 'sin_definir',
                'operaciones' => (int) $tipo->operaciones,
                'monto' => round((float) $tipo->monto, 2),
            ]);

        $metodosPago = Pago::query()
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('metodo, COUNT(*) as operaciones, COALESCE(SUM(monto), 0) as monto')
            ->groupBy('metodo')
            ->orderByDesc('monto')
            ->get()
            ->map(fn ($metodo) => [
                'metodo' => $metodo->metodo ?: 'Sin especificar',
                'operaciones' => (int) $metodo->operaciones,
                'monto' => round((float) $metodo->monto, 2),
            ]);

        return [
            'top_productos' => $topProductos,
            'top_clientes' => $topClientes,
            'categorias' => $categorias,
            'tipos_pago' => $tiposPago,
            'metodos_pago' => $metodosPago,
        ];
    }

    private function datosInventario(Carbon $inicio, Carbon $fin): array
    {
        $items = $this->itemsInventario();
        $hoy = Carbon::now()->startOfDay();
        $limite = $hoy->copy()->addDays(90);

        $lotesBase = fn () => Lote::query()
            ->where('stock', '>', 0)
            ->whereNotNull('fecha_caducidad')
            ->whereDate('fecha_caducidad', '<=', $limite->toDateString());

        $totalLotesPorVencer = $lotesBase()->count();
        $totalLotesVencidos = $lotesBase()
            ->whereDate('fecha_caducidad', '<', $hoy->toDateString())
            ->count();

        $lotes = $lotesBase()
            ->with([
                'productVariant.product:id,name',
                'productVariant.unitMeasure:id,symbol',
                'productVariant.unidadBase:id,symbol',
            ])
            ->orderBy('fecha_caducidad')
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(function (Lote $lote) use ($hoy) {
                $fecha = $lote->fecha_caducidad?->copy()->startOfDay();
                $dias = $fecha
                    ? (int) floor(($fecha->timestamp - $hoy->timestamp) / 86400)
                    : null;

                return [
                    'id' => (int) $lote->id,
                    'lote' => $lote->codigo,
                    'producto' => $lote->productVariant?->product?->name ?? 'Producto eliminado',
                    'presentacion' => $lote->productVariant?->presentacion ?? '',
                    'sku' => $lote->productVariant?->sku ?? '',
                    'fecha_caducidad' => $fecha?->toDateString(),
                    'stock' => round((float) $lote->stock, 2),
                    'unidad' => $lote->productVariant?->unidadBase?->symbol
                        ?? $lote->productVariant?->unitMeasure?->symbol
                        ?? 'und',
                    'dias' => $dias,
                    'vencido' => $dias !== null && $dias < 0,
                ];
            });

        $categorias = $items
            ->groupBy('categoria')
            ->map(fn (Collection $grupo, string $categoria) => [
                'categoria' => $categoria,
                'presentaciones' => $grupo->count(),
                'stock' => round((float) $grupo->sum('stock'), 2),
                'valor' => round((float) $grupo->sum('valor'), 2),
            ])
            ->sortByDesc('valor')
            ->values();

        $itemsStockBajo = $items
            ->filter(fn (array $item) => $item['stock'] <= $item['minimo']);

        $stockBajo = $itemsStockBajo
            ->sortBy('stock')
            ->take(50)
            ->values();

        $productosValor = $items
            ->sortByDesc('valor')
            ->take(10)
            ->values();

        return [
            'resumen' => [
                'presentaciones' => $items->count(),
                'productos' => $items->pluck('producto_id')->unique()->count(),
                'stock_total' => round((float) $items->sum('stock'), 2),
                'valor' => round((float) $items->sum('valor'), 2),
                'valor_comercial' => round((float) $items->sum('valor_comercial'), 2),
                'stock_bajo' => $itemsStockBajo->count(),
                'lotes_por_vencer' => $totalLotesPorVencer,
                'lotes_vencidos' => $totalLotesVencidos,
            ],
            'categorias' => $categorias,
            'productos_valor' => $productosValor,
            'stock_bajo' => $stockBajo,
            'lotes' => $lotes,
            'movimientos' => [
                'ingresos' => (int) Ingreso::query()
                    ->where('estado', 'activo')
                    ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
                    ->count(),
                'salidas' => (int) Salida::query()
                    ->where('estado', 'activo')
                    ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
                    ->count(),
                'unidades_ingresadas' => round((float) IngresoDetalle::query()
                    ->whereHas('ingreso', fn ($query) => $query
                        ->where('estado', 'activo')
                        ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()]))
                    ->sum('cantidad'), 2),
                'unidades_despachadas' => round((float) SalidaDetalle::query()
                    ->whereHas('salida', fn ($query) => $query
                        ->where('estado', 'activo')
                        ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()]))
                    ->sum('cantidad'), 2),
            ],
        ];
    }

    private function itemsInventario(): Collection
    {
        $costos = IngresoDetalle::query()
            ->join('ingresos', 'ingresos.id', '=', 'ingreso_detalles.ingreso_id')
            ->where('ingresos.estado', 'activo')
            ->whereNotNull('ingreso_detalles.product_variant_id')
            ->selectRaw('ingreso_detalles.product_variant_id, SUM(ingreso_detalles.subtotal) / NULLIF(SUM(ingreso_detalles.cantidad), 0) as costo')
            ->groupBy('ingreso_detalles.product_variant_id')
            ->pluck('costo', 'product_variant_id');

        return ProductVariant::query()
            ->where('is_active', true)
            ->with([
                'product.category:id,name',
                'unitMeasure:id,symbol',
                'unidadBase:id,symbol',
            ])
            ->conStock()
            ->get()
            ->map(function (ProductVariant $variante) use ($costos) {
                $stock = round(max(0, (float) $variante->stock), 2);
                $minimo = round((float) $variante->minimum_stock, 2);
                $precio = round((float) $variante->unit_price, 2);
                $costo = round((float) ($costos[$variante->id] ?? $precio), 2);

                return [
                    'id' => (int) $variante->id,
                    'producto_id' => (int) $variante->product_id,
                    'producto' => $variante->product?->name ?? 'Producto eliminado',
                    'presentacion' => $variante->presentacion,
                    'sku' => $variante->sku,
                    'categoria' => $variante->product?->category?->name ?? 'Sin categoría',
                    'unidad' => $variante->unidadBase?->symbol ?? $variante->unitMeasure?->symbol ?? 'und',
                    'stock' => $stock,
                    'minimo' => $minimo,
                    'costo' => $costo,
                    'precio' => $precio,
                    'valor' => round($stock * $costo, 2),
                    'valor_comercial' => round($stock * $precio, 2),
                    'faltante' => round(max(0, $minimo - $stock), 2),
                ];
            })
            ->values();
    }

    private function datosIngresos(Carbon $inicio, Carbon $fin): array
    {
        $desde = $inicio->toDateString();
        $hasta = $fin->toDateString();

        $topProductos = IngresoDetalle::query()
            ->selectRaw('product_variant_id, SUM(cantidad) as cantidad, SUM(subtotal) as monto')
            ->whereHas('ingreso', fn ($query) => $query
                ->where('estado', 'activo')
                ->whereBetween('fecha', [$desde, $hasta]))
            ->with([
                'productVariant.product:id,name',
                'productVariant.unitMeasure:id,symbol',
                'productVariant.unidadBase:id,symbol',
            ])
            ->groupBy('product_variant_id')
            ->orderByDesc('monto')
            ->limit(10)
            ->get()
            ->map(fn (IngresoDetalle $detalle) => [
                'id' => (int) $detalle->product_variant_id,
                'producto' => $detalle->productVariant?->product?->name ?? 'Producto eliminado',
                'presentacion' => $detalle->productVariant?->presentacion ?? '',
                'sku' => $detalle->productVariant?->sku ?? '',
                'unidad' => $detalle->productVariant?->unidadBase?->symbol
                    ?? $detalle->productVariant?->unitMeasure?->symbol
                    ?? 'und',
                'cantidad' => round((float) $detalle->cantidad, 2),
                'monto' => round((float) $detalle->monto, 2),
            ]);

        $encargados = Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$desde, $hasta])
            ->with('encargado:id,first_name,first_surname,second_surname')
            ->selectRaw('encargado_id, COUNT(*) as ingresos, COALESCE(SUM(total), 0) as monto')
            ->groupBy('encargado_id')
            ->orderByDesc('monto')
            ->get()
            ->map(fn (Ingreso $ingreso) => [
                'encargado' => $ingreso->encargado?->full_name ?: 'Sin encargado',
                'ingresos' => (int) $ingreso->ingresos,
                'monto' => round((float) $ingreso->monto, 2),
            ]);

        return [
            'top_productos' => $topProductos,
            'encargados' => $encargados,
        ];
    }

    private function datosCobranzas(Carbon $inicio, Carbon $fin): array
    {
        $desde = $inicio->toDateString();
        $hasta = $fin->toDateString();
        $hoy = Carbon::now()->startOfDay();

        $cartera = Venta::query()
            ->where('estado', 'activo')
            ->where('pago_tipo', 'credito')
            ->where('saldo_pendiente', '>', 0)
            ->get(['id', 'fecha', 'saldo_pendiente', 'cliente_id', 'cliente']);

        $buckets = collect([
            ['clave' => '0_30', 'etiqueta' => '0 a 30 días', 'monto' => 0.0, 'operaciones' => 0],
            ['clave' => '31_60', 'etiqueta' => '31 a 60 días', 'monto' => 0.0, 'operaciones' => 0],
            ['clave' => '61_90', 'etiqueta' => '61 a 90 días', 'monto' => 0.0, 'operaciones' => 0],
            ['clave' => 'mas_90', 'etiqueta' => 'Más de 90 días', 'monto' => 0.0, 'operaciones' => 0],
        ]);

        foreach ($cartera as $venta) {
            $fecha = $venta->fecha?->copy()->startOfDay() ?? $hoy;
            $dias = max(0, (int) floor(($hoy->timestamp - $fecha->timestamp) / 86400));

            $indice = $dias <= 30 ? 0 : ($dias <= 60 ? 1 : ($dias <= 90 ? 2 : 3));

            $bucket = $buckets[$indice];
            $bucket['monto'] += (float) $venta->saldo_pendiente;
            $bucket['operaciones']++;
            $buckets[$indice] = $bucket;
        }

        $totalCartera = round((float) $buckets->sum('monto'), 2);

        $antiguedad = $buckets
            ->map(fn (array $bucket) => [
                'clave' => $bucket['clave'],
                'etiqueta' => $bucket['etiqueta'],
                'monto' => round($bucket['monto'], 2),
                'operaciones' => $bucket['operaciones'],
                'porcentaje' => $totalCartera > 0
                    ? round(($bucket['monto'] / $totalCartera) * 100, 1)
                    : 0,
            ])
            ->values();

        $topDeudores = Venta::query()
            ->leftJoin('clientes', 'clientes.id', '=', 'ventas.cliente_id')
            ->where('ventas.estado', 'activo')
            ->where('ventas.pago_tipo', 'credito')
            ->where('ventas.saldo_pendiente', '>', 0)
            ->selectRaw("COALESCE(clientes.nombre, ventas.cliente, 'Consumidor final') as cliente, COUNT(*) as operaciones, COALESCE(SUM(ventas.saldo_pendiente), 0) as saldo")
            ->groupByRaw("COALESCE(clientes.nombre, ventas.cliente, 'Consumidor final')")
            ->orderByDesc('saldo')
            ->limit(10)
            ->get()
            ->map(fn ($deudor) => [
                'cliente' => $deudor->cliente,
                'operaciones' => (int) $deudor->operaciones,
                'saldo' => round((float) $deudor->saldo, 2),
            ]);

        $metodosPago = Pago::query()
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('metodo, COUNT(*) as operaciones, COALESCE(SUM(monto), 0) as monto')
            ->groupBy('metodo')
            ->orderByDesc('monto')
            ->get()
            ->map(fn ($metodo) => [
                'metodo' => $metodo->metodo ?: 'Sin especificar',
                'operaciones' => (int) $metodo->operaciones,
                'monto' => round((float) $metodo->monto, 2),
            ]);

        return [
            'antiguedad' => $antiguedad,
            'total_cartera' => $totalCartera,
            'top_deudores' => $topDeudores,
            'metodos_pago' => $metodosPago,
        ];
    }

    private function construirTendencia(Carbon $inicio, Carbon $fin, string $granularidad): array
    {
        $mensual = $granularidad === 'mes';
        $formato = $mensual ? 'Y-m' : 'Y-m-d';
        $desde = $inicio->toDateString();
        $hasta = $fin->toDateString();

        $ventas = Venta::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$desde, $hasta])
            ->get(['fecha', 'total'])
            ->groupBy(fn (Venta $venta) => $venta->fecha?->format($formato) ?? '')
            ->map(fn (Collection $grupo) => round((float) $grupo->sum('total'), 2));

        $compras = Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$desde, $hasta])
            ->get(['fecha', 'total'])
            ->groupBy(fn (Ingreso $ingreso) => $ingreso->fecha?->format($formato) ?? '')
            ->map(fn (Collection $grupo) => round((float) $grupo->sum('total'), 2));

        $pagos = Pago::query()
            ->whereBetween('fecha', [$desde, $hasta])
            ->get(['fecha', 'monto'])
            ->groupBy(fn (Pago $pago) => $pago->fecha?->format($formato) ?? '')
            ->map(fn (Collection $grupo) => round((float) $grupo->sum('monto'), 2));

        $claves = [];
        $etiquetas = [];
        $cursor = $mensual ? $inicio->copy()->startOfMonth() : $inicio->copy();
        $ultimo = $mensual ? $fin->copy()->startOfMonth() : $fin->copy();

        while ($cursor->lte($ultimo)) {
            $clave = $cursor->format($formato);
            $claves[] = $clave;
            $etiquetas[] = ucfirst($cursor->copy()->locale('es')->translatedFormat($mensual ? 'M Y' : 'd M'));
            $cursor = $mensual ? $cursor->copy()->addMonth() : $cursor->copy()->addDay();
        }

        $valores = fn (Collection $totales) => collect($claves)
            ->map(fn (string $clave) => round((float) ($totales[$clave] ?? 0), 2))
            ->all();

        return [
            'labels' => $etiquetas,
            'series' => [
                ['nombre' => 'Ventas', 'color' => '#10b981', 'valores' => $valores($ventas)],
                ['nombre' => 'Compras', 'color' => '#6366f1', 'valores' => $valores($compras)],
                ['nombre' => 'Cobros', 'color' => '#f59e0b', 'valores' => $valores($pagos)],
            ],
        ];
    }

    private function detalleSeccion(string $seccion, Carbon $inicio, Carbon $fin)
    {
        $desde = $inicio->toDateString();
        $hasta = $fin->toDateString();

        if ($seccion === 'ventas') {
            return Venta::query()
                ->with(['clienteRelacion:id,nombre', 'usuario:id,name'])
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->paginate(12, [
                    'id',
                    'numero',
                    'fecha',
                    'cliente',
                    'cliente_id',
                    'total',
                    'pago_tipo',
                    'saldo_pendiente',
                    'estado',
                    'user_id',
                ], 'pagina')
                ->withQueryString();
        }

        if ($seccion === 'ingresos') {
            return Ingreso::query()
                ->with(['encargado:id,first_name,first_surname,second_surname', 'usuario:id,name'])
                ->withCount('detalles')
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->paginate(12, [
                    'id',
                    'numero',
                    'fecha',
                    'encargado_id',
                    'total',
                    'estado',
                    'user_id',
                ], 'pagina')
                ->withQueryString();
        }

        if ($seccion === 'cobranzas') {
            return Pago::query()
                ->with([
                    'venta:id,numero,fecha,cliente,cliente_id,total,saldo_pendiente,pago_tipo,estado',
                    'venta.clienteRelacion:id,nombre',
                    'usuario:id,name',
                ])
                ->whereBetween('fecha', [$desde, $hasta])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->paginate(12, [
                    'id',
                    'venta_id',
                    'fecha',
                    'monto',
                    'metodo',
                    'observaciones',
                    'user_id',
                ], 'pagina')
                ->withQueryString();
        }

        return null;
    }

    private function exportarResumen(array $base, array $inventario, array $periodo): StreamedResponse
    {
        $resumen = $base['resumen'];

        $filas = [
            ['Ventas', number_format($resumen['ventas']['total'], 2, '.', '')],
            ['Cantidad de ventas', $resumen['ventas']['cantidad']],
            ['Ticket promedio', number_format($resumen['ventas']['ticket_promedio'], 2, '.', '')],
            ['Ventas anuladas', $resumen['ventas']['anuladas']],
            ['Compras', number_format($resumen['compras']['total'], 2, '.', '')],
            ['Cantidad de compras', $resumen['compras']['cantidad']],
            ['Cobros', number_format($resumen['cobros']['total'], 2, '.', '')],
            ['Cantidad de cobros', $resumen['cobros']['cantidad']],
            ['Saldo por cobrar', number_format($resumen['ventas']['por_cobrar'], 2, '.', '')],
            ['Operaciones a crédito pendientes', $resumen['ventas']['credito_pendiente_operaciones']],
            ['Clientes con deuda', $resumen['ventas']['clientes_con_deuda']],
            ['Presentaciones activas', $inventario['presentaciones']],
            ['Stock total', number_format($inventario['stock_total'], 2, '.', '')],
            ['Valor de inventario al costo', number_format($inventario['valor'], 2, '.', '')],
            ['Valor comercial del inventario', number_format($inventario['valor_comercial'], 2, '.', '')],
            ['Presentaciones con stock bajo', $inventario['stock_bajo']],
            ['Lotes por vencer (90 días)', $inventario['lotes_por_vencer']],
            ['Lotes vencidos', $inventario['lotes_vencidos']],
        ];

        return $this->descargarCsv(
            'informe-resumen-'.$periodo['inicio'].'_'.$periodo['fin'].'.csv',
            ['Indicador', 'Valor'],
            $filas
        );
    }

    private function exportarVentas(Carbon $inicio, Carbon $fin, array $periodo): StreamedResponse
    {
        $filas = Venta::query()
            ->with(['clienteRelacion:id,nombre', 'usuario:id,name'])
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Venta $venta) => [
                $venta->numero,
                $venta->fecha?->format('Y-m-d'),
                $venta->clienteRelacion?->nombre ?? $venta->cliente ?? 'Consumidor final',
                $venta->pago_tipo,
                number_format((float) $venta->total, 2, '.', ''),
                number_format((float) $venta->saldo_pendiente, 2, '.', ''),
                $venta->estado,
                $venta->usuario?->name ?? '',
            ]);

        return $this->descargarCsv(
            'informe-ventas-'.$periodo['inicio'].'_'.$periodo['fin'].'.csv',
            ['Número', 'Fecha', 'Cliente', 'Tipo de pago', 'Total', 'Saldo pendiente', 'Estado', 'Registrado por'],
            $filas
        );
    }

    private function exportarIngresos(Carbon $inicio, Carbon $fin, array $periodo): StreamedResponse
    {
        $filas = Ingreso::query()
            ->with(['encargado:id,first_name,first_surname,second_surname', 'usuario:id,name'])
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Ingreso $ingreso) => [
                $ingreso->numero,
                $ingreso->fecha?->format('Y-m-d'),
                $ingreso->encargado?->full_name ?? 'Sin encargado',
                number_format((float) $ingreso->total, 2, '.', ''),
                $ingreso->estado,
                $ingreso->usuario?->name ?? '',
            ]);

        return $this->descargarCsv(
            'informe-ingresos-'.$periodo['inicio'].'_'.$periodo['fin'].'.csv',
            ['Número', 'Fecha', 'Encargado', 'Total', 'Estado', 'Registrado por'],
            $filas
        );
    }

    private function exportarCobranzas(Carbon $inicio, Carbon $fin, array $periodo): StreamedResponse
    {
        $filas = Pago::query()
            ->with([
                'venta:id,numero,cliente,cliente_id,saldo_pendiente',
                'venta.clienteRelacion:id,nombre',
                'usuario:id,name',
            ])
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Pago $pago) => [
                $pago->venta?->numero ?? '',
                $pago->fecha?->format('Y-m-d'),
                $pago->venta?->clienteRelacion?->nombre ?? $pago->venta?->cliente ?? 'Consumidor final',
                $pago->metodo ?: 'Sin especificar',
                number_format((float) $pago->monto, 2, '.', ''),
                number_format((float) ($pago->venta?->saldo_pendiente ?? 0), 2, '.', ''),
                $pago->usuario?->name ?? '',
            ]);

        return $this->descargarCsv(
            'informe-cobranzas-'.$periodo['inicio'].'_'.$periodo['fin'].'.csv',
            ['Venta', 'Fecha de pago', 'Cliente', 'Método', 'Monto', 'Saldo actual', 'Registrado por'],
            $filas
        );
    }

    private function exportarInventario(array $periodo): StreamedResponse
    {
        $filas = $this->itemsInventario()
            ->map(fn (array $item) => [
                $item['producto'],
                $item['presentacion'],
                $item['sku'],
                $item['categoria'],
                number_format($item['stock'], 2, '.', ''),
                $item['unidad'],
                number_format($item['minimo'], 2, '.', ''),
                number_format($item['costo'], 2, '.', ''),
                number_format($item['valor'], 2, '.', ''),
                $item['stock'] <= $item['minimo'] ? 'Stock bajo' : 'Normal',
            ]);

        return $this->descargarCsv(
            'informe-inventario-'.$periodo['inicio'].'_'.$periodo['fin'].'.csv',
            ['Producto', 'Presentación', 'SKU', 'Categoría', 'Stock', 'Unidad', 'Mínimo', 'Costo promedio', 'Valor al costo', 'Estado'],
            $filas
        );
    }

    private function descargarCsv(string $nombre, array $encabezados, Collection|array $filas): StreamedResponse
    {
        return response()->streamDownload(function () use ($encabezados, $filas) {
            $salida = fopen('php://output', 'w');

            if ($salida === false) {
                return;
            }

            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, $encabezados, ';');

            foreach ($filas as $fila) {
                fputcsv($salida, $fila, ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
