<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InformesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $esAdministrador = $user->hasRole('Administrador');

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $query = Ingreso::query()
            ->when($fechaInicio, fn ($q) => $q->where('fecha', '>=', $fechaInicio))
            ->when($fechaFin, fn ($q) => $q->where('fecha', '<=', $fechaFin));

        $resumen = (clone $query)->select(
            DB::raw('COUNT(*) as total_ingresos'),
            DB::raw('COALESCE(SUM(total), 0) as monto_total'),
            DB::raw('COALESCE(SUM(CASE WHEN estado = \'activo\' THEN total ELSE 0 END), 0) as monto_activos'),
            DB::raw('SUM(CASE WHEN estado = \'anulado\' THEN 1 ELSE 0 END) as total_anulados'),
        )->first();

        $productosStockBajo = ProductVariant::where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        $productosTotal = ProductVariant::where('is_active', true)->count();

        $movimientosQuery = Ingreso::with([
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->withCount('detalles')
            ->when($fechaInicio, fn ($q) => $q->where('fecha', '>=', $fechaInicio))
            ->when($fechaFin, fn ($q) => $q->where('fecha', '<=', $fechaFin))
            ->latest('fecha')
            ->latest('id');

        if ($esAdministrador) {
            $movimientosQuery->with('usuario');
        }

        $movimientos = $movimientosQuery->paginate(15)->withQueryString();

        return Inertia::render('Informes/Index', [
            'resumen' => $resumen,
            'movimientos' => $movimientos,
            'productosStockBajo' => $productosStockBajo,
            'productosTotal' => $productosTotal,
            'esAdministrador' => $esAdministrador,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }
}
