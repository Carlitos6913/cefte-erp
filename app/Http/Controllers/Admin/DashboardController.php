<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use App\Models\ProductVariant;
use App\Models\Salida;
use App\Models\Venta;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $hoy = Carbon::now()->toDateString();

        $ventasHoy = Venta::where('fecha', $hoy)
            ->where('estado', 'activo')
            ->sum('total');

        $cuentasPorCobrar = Venta::where('pago_tipo', 'credito')
            ->where('saldo_pendiente', '>', 0)
            ->where('estado', 'activo')
            ->sum('saldo_pendiente');

        $totalProductos = ProductVariant::where('is_active', true)->count();

        $stockBajo = ProductVariant::where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        $ventasRecientes = Venta::with(['clienteRelacion', 'detalles.productVariant.product'])
            ->where('estado', 'activo')
            ->latest('fecha')
            ->latest('id')
            ->get();

        $ingresosRecientes = Ingreso::with(['usuario', 'detalles.productVariant.product'])
            ->where('estado', 'activo')
            ->latest('fecha')
            ->latest('id')
            ->get();

        $salidasRecientes = Salida::with(['usuario', 'detalles.productVariant.product'])
            ->where('estado', 'activo')
            ->latest('fecha')
            ->latest('id')
            ->get();

        return Inertia::render('Dashboard', [
            'ventasHoy' => $ventasHoy,
            'cuentasPorCobrar' => $cuentasPorCobrar,
            'totalProductos' => $totalProductos,
            'stockBajo' => $stockBajo,
            'ventasRecientes' => $ventasRecientes,
            'ingresosRecientes' => $ingresosRecientes,
            'salidasRecientes' => $salidasRecientes,
        ]);
    }
}
