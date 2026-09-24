<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ingreso;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IngresoController extends Controller
{
    public function index()
    {
        $ingresos = Ingreso::with([
            'usuario',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->withCount('detalles')
            ->latest('fecha')
            ->latest('id')
            ->paginate(10);

        return Inertia::render('Inventario/Ingresos/Index', [
            'ingresos' => $ingresos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Ingresos/Create', [
            'categorias' => Category::orderBy('name')->get(['id', 'name']),
            'productos' => ProductVariant::where('is_active', true)
                ->with('unitMeasure:id,name,symbol')
                ->with('product:id,name,code,category_id')
                ->orderBy('sku')
                ->get([
                    'id',
                    'product_id',
                    'unit_measure_id',
                    'sku',
                    'unit_price',
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => [
                'required',
                'date',
            ],

            'proveedor' => [
                'nullable',
                'string',
                'max:255',
            ],

            'observaciones' => [
                'nullable',
                'string',
            ],

            'detalles' => [
                'required',
                'array',
                'min:1',
            ],

            'detalles.*.product_variant_id' => [
                'required',
                'exists:product_variants,id',
            ],

            'detalles.*.cantidad' => [
                'required',
                'integer',
                'min:1',
            ],

            'detalles.*.precio_unitario' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        DB::transaction(function () use ($validated) {

            $numero = 'ING-'.now()->format('YmdHis');

            $total = collect($validated['detalles'])
                ->sum(function ($detalle) {
                    return $detalle['cantidad']
                        * $detalle['precio_unitario'];
                });

            $ingreso = Ingreso::create([
                'numero' => $numero,
                'fecha' => $validated['fecha'],
                'proveedor' => $validated['proveedor'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => $total,
                'estado' => 'activo',
                'user_id' => Auth::id(),
            ]);

            foreach ($validated['detalles'] as $detalle) {

                $subtotal =
                    $detalle['cantidad']
                    * $detalle['precio_unitario'];

                $ingreso->detalles()->create([
                    'product_variant_id' => $detalle['product_variant_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);

                ProductVariant::where('id', $detalle['product_variant_id'])
                    ->increment('stock', $detalle['cantidad']);
            }
        });

        return redirect()
            ->route('ingresos.index')
            ->with('success', 'Ingreso registrado correctamente.');
    }

    public function show(string $id)
    {
        $ingreso = Ingreso::with([
            'usuario',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])->findOrFail($id);

        return Inertia::render('Inventario/Ingresos/Show', [
            'ingreso' => $ingreso,
        ]);
    }

    public function edit(string $id)
    {
        $ingreso = Ingreso::with([
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
        ])
            ->findOrFail($id);

        if ($ingreso->estado === 'anulado') {
            return redirect()
                ->route('ingresos.show', $ingreso->id)
                ->with('error', 'No se puede editar un ingreso anulado.');
        }

        return Inertia::render('Inventario/Ingresos/Edit', [
            'ingreso' => $ingreso,
            'categorias' => Category::orderBy('name')->get(['id', 'name']),
            'productos' => ProductVariant::where('is_active', true)
                ->with('unitMeasure:id,name,symbol')
                ->with('product:id,name,code,category_id')
                ->orderBy('sku')
                ->get([
                    'id',
                    'product_id',
                    'unit_measure_id',
                    'sku',
                    'unit_price',
                ]),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $ingreso = Ingreso::with('detalles')
            ->findOrFail($id);

        if ($ingreso->estado === 'anulado') {
            return back()->with(
                'error',
                'No se puede modificar un ingreso anulado.'
            );
        }

        $validated = $request->validate([
            'fecha' => [
                'required',
                'date',
            ],

            'proveedor' => [
                'nullable',
                'string',
                'max:255',
            ],

            'observaciones' => [
                'nullable',
                'string',
            ],

            'detalles' => [
                'required',
                'array',
                'min:1',
            ],

            'detalles.*.product_variant_id' => [
                'required',
                'exists:product_variants,id',
            ],

            'detalles.*.cantidad' => [
                'required',
                'integer',
                'min:1',
            ],

            'detalles.*.precio_unitario' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        DB::transaction(function () use ($ingreso, $validated) {

            /*
             * 1. Revertir el stock generado por
             *    el ingreso anterior.
             */
            foreach ($ingreso->detalles as $detalle) {

                ProductVariant::where('id', $detalle->product_variant_id)
                    ->decrement('stock', $detalle->cantidad);
            }

            /*
             * 2. Eliminar los detalles anteriores.
             */
            $ingreso->detalles()->delete();

            /*
             * 3. Calcular el nuevo total.
             */
            $total = collect($validated['detalles'])
                ->sum(function ($detalle) {
                    return $detalle['cantidad']
                        * $detalle['precio_unitario'];
                });

            /*
             * 4. Actualizar información general.
             */
            $ingreso->update([
                'fecha' => $validated['fecha'],
                'proveedor' => $validated['proveedor'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => $total,
            ]);

            /*
             * 5. Crear los nuevos detalles
             *    y aplicar el nuevo stock.
             */
            foreach ($validated['detalles'] as $detalle) {

                $subtotal =
                    $detalle['cantidad']
                    * $detalle['precio_unitario'];

                $ingreso->detalles()->create([
                    'product_variant_id' => $detalle['product_variant_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);

                ProductVariant::where('id', $detalle['product_variant_id'])
                    ->increment('stock', $detalle['cantidad']);
            }
        });

        return redirect()
            ->route('ingresos.show', $ingreso->id)
            ->with('success', 'Ingreso actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $ingreso = Ingreso::with('detalles')
            ->findOrFail($id);

        if ($ingreso->estado === 'anulado') {
            return back()->with(
                'error',
                'El ingreso ya se encuentra anulado.'
            );
        }

        DB::transaction(function () use ($ingreso) {

            /*
             * Revertir el stock.
             */
            foreach ($ingreso->detalles as $detalle) {

                ProductVariant::where('id', $detalle->product_variant_id)
                    ->decrement('stock', $detalle->cantidad);
            }

            /*
             * No eliminamos el registro.
             * Lo marcamos como anulado.
             */
            $ingreso->update([
                'estado' => 'anulado',
            ]);
        });

        return redirect()
            ->route('ingresos.index')
            ->with('success', 'Ingreso anulado correctamente.');
    }
}
