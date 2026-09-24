<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;

        $ventasConDeuda = Venta::where('pago_tipo', 'credito')
            ->where('saldo_pendiente', '>', 0)
            ->where('estado', 'activo')
            ->with(['clienteRelacion', 'usuario'])
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero', 'like', "%{$buscar}%")
                        ->orWhereHas('clienteRelacion', function ($cq) use ($buscar) {
                            $cq->where('nombre', 'like', "%{$buscar}%");
                        })
                        ->orWhere('cliente', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('fecha', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Pagos/Index', [
            'ventasConDeuda' => $ventasConDeuda,
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $ventaId = $request->venta_id;

        $venta = Venta::with(['clienteRelacion', 'detalles.producto', 'pagos'])
            ->findOrFail($ventaId);

        if ($venta->saldo_pendiente <= 0) {
            return redirect()
                ->route('pagos.show', $venta->id)
                ->with('error', 'Esta venta ya está pagada completamente.');
        }

        return Inertia::render('Pagos/Create', [
            'venta' => $venta,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'venta_id' => ['required', 'exists:ventas,id'],
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo' => ['required', 'string', 'in:efectivo,transferencia,qr,otro'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $venta = Venta::findOrFail($validated['venta_id']);

        if ($venta->saldo_pendiente <= 0) {
            return back()->with('error', 'Esta venta ya está pagada completamente.');
        }

        if ($validated['monto'] > $venta->saldo_pendiente) {
            return back()->withErrors([
                'monto' => 'El monto excede el saldo pendiente de Bs. '.number_format($venta->saldo_pendiente, 2),
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $venta) {

            Pago::create([
                'venta_id' => $validated['venta_id'],
                'fecha' => $validated['fecha'],
                'monto' => $validated['monto'],
                'metodo' => $validated['metodo'],
                'observaciones' => $validated['observaciones'] ?? null,
                'user_id' => Auth::id(),
            ]);

            $nuevoSaldo = $venta->saldo_pendiente - $validated['monto'];

            $venta->update([
                'saldo_pendiente' => max(0, $nuevoSaldo),
            ]);
        });

        return redirect()
            ->route('pagos.show', $venta->id)
            ->with('success', 'Pago registrado correctamente.');
    }

    public function show(Venta $venta)
    {
        $venta->load(['clienteRelacion', 'usuario', 'pagos.usuario', 'detalles.producto']);

        return Inertia::render('Pagos/Show', [
            'venta' => $venta,
        ]);
    }
}
