<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use App\Models\Lote;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Salida;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $hoy = Carbon::now()->toDateString();
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();
        $inicioMesAnterior = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $finMesAnterior = Carbon::now()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $ventasHoyConsulta = Venta::where('fecha', $hoy)
            ->where('estado', 'activo');

        $ventasHoy = (float) (clone $ventasHoyConsulta)->sum('total');
        $ventasHoyCantidad = (clone $ventasHoyConsulta)->count();

        $ventasAyer = (float) Venta::where('fecha', Carbon::yesterday()->toDateString())
            ->where('estado', 'activo')
            ->sum('total');

        $ventasMesConsulta = Venta::whereBetween('fecha', [$inicioMes, $finMes])
            ->where('estado', 'activo');

        $ventasMes = (float) (clone $ventasMesConsulta)->sum('total');
        $ventasMesCantidad = (clone $ventasMesConsulta)->count();

        $ventasMesAnterior = (float) Venta::whereBetween('fecha', [$inicioMesAnterior, $finMesAnterior])
            ->where('estado', 'activo')
            ->sum('total');

        $cuentasPorCobrarConsulta = Venta::where('pago_tipo', 'credito')
            ->where('saldo_pendiente', '>', 0)
            ->where('estado', 'activo');

        $cuentasPorCobrar = (float) (clone $cuentasPorCobrarConsulta)->sum('saldo_pendiente');
        $ventasCreditoPendientes = (clone $cuentasPorCobrarConsulta)->count();

        $totalProductos = Product::where('is_active', true)->count();
        $totalPresentaciones = ProductVariant::where('is_active', true)->count();

        $stockBajo = ProductVariant::where('is_active', true)
            ->whereRaw(
                '(SELECT COALESCE(SUM(lotes.stock), 0) FROM lotes WHERE lotes.product_variant_id = product_variants.id) <= product_variants.minimum_stock'
            )
            ->count();

        $stockCritico = ProductVariant::where('is_active', true)
            ->conStock()
            ->with([
                'product:id,name,category_id',
                'product.category:id,name',
                'unitMeasure:id,name,symbol',
                'unidadBase:id,name,symbol',
            ])
            ->whereRaw(
                '(SELECT COALESCE(SUM(lotes.stock), 0) FROM lotes WHERE lotes.product_variant_id = product_variants.id) <= product_variants.minimum_stock'
            )
            ->orderBy('stock_agregado')
            ->limit(5)
            ->get();

        $porVencer = Lote::with([
            'productVariant.product:id,name',
            'productVariant.unitMeasure:id,name,symbol',
            'productVariant.unidadBase:id,name,symbol',
        ])
            ->where('stock', '>', 0)
            ->whereDate('fecha_caducidad', '<=', Carbon::now()->addDays(30)->toDateString())
            ->orderBy('fecha_caducidad')
            ->limit(5)
            ->get();

        $porVencerTotal = Lote::where('stock', '>', 0)
            ->whereDate('fecha_caducidad', '<=', Carbon::now()->addDays(30)->toDateString())
            ->count();

        $ventasRecientes = Venta::with(['clienteRelacion', 'detalles.productVariant.product'])
            ->where('estado', 'activo')
            ->latest('fecha')
            ->latest('id')
            ->limit(6)
            ->get();

        $ingresosRecientes = Ingreso::with(['usuario', 'detalles.productVariant.product'])
            ->where('estado', 'activo')
            ->latest('fecha')
            ->latest('id')
            ->limit(6)
            ->get();

        $salidasRecientes = Salida::with(['usuario', 'detalles.productVariant.product'])
            ->where('estado', 'activo')
            ->latest('fecha')
            ->latest('id')
            ->limit(6)
            ->get();

        $ventasSemana = Venta::whereBetween('fecha', [
            Carbon::now()->subDays(6)->toDateString(),
            $hoy,
        ])
            ->where('estado', 'activo')
            ->get(['fecha', 'total']);

        $totalesPorDia = $ventasSemana
            ->groupBy(fn (Venta $venta) => $venta->fecha->toDateString())
            ->map(fn ($ventas) => (float) $ventas->sum('total'));

        $ventasUltimos7Dias = collect(range(6, 0))
            ->map(function (int $dias) use ($totalesPorDia) {
                $fecha = Carbon::now()->subDays($dias)->toDateString();

                return [
                    'fecha' => $fecha,
                    'total' => (float) ($totalesPorDia[$fecha] ?? 0),
                ];
            })
            ->values();

        $topProductos = VentaDetalle::query()
            ->selectRaw('product_variant_id, SUM(cantidad) as total_cantidad, SUM(subtotal) as total_monto')
            ->whereHas('venta', function ($query) use ($inicioMes, $finMes) {
                $query->where('estado', 'activo')
                    ->whereBetween('fecha', [$inicioMes, $finMes]);
            })
            ->with([
                'productVariant.product:id,name',
                'productVariant.unidadBase:id,name,symbol',
                'productVariant.unitMeasure:id,name,symbol',
            ])
            ->groupBy('product_variant_id')
            ->orderByDesc('total_cantidad')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'ventasHoy' => $ventasHoy,
            'ventasHoyCantidad' => $ventasHoyCantidad,
            'ventasAyer' => $ventasAyer,
            'ventasMes' => $ventasMes,
            'ventasMesCantidad' => $ventasMesCantidad,
            'ventasMesAnterior' => $ventasMesAnterior,
            'ticketPromedio' => $ventasMesCantidad > 0 ? round($ventasMes / $ventasMesCantidad, 2) : 0,
            'cuentasPorCobrar' => $cuentasPorCobrar,
            'ventasCreditoPendientes' => $ventasCreditoPendientes,
            'totalProductos' => $totalProductos,
            'totalPresentaciones' => $totalPresentaciones,
            'stockBajo' => $stockBajo,
            'stockCritico' => $stockCritico,
            'porVencer' => $porVencer,
            'porVencerTotal' => $porVencerTotal,
            'ventasRecientes' => $ventasRecientes,
            'ingresosRecientes' => $ingresosRecientes,
            'salidasRecientes' => $salidasRecientes,
            'ventasUltimos7Dias' => $ventasUltimos7Dias,
            'topProductos' => $topProductos,
        ]);
    }
}
