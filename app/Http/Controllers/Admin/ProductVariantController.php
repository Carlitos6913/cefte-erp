<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UnitMeasure;
use App\Services\CodigoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductVariantController extends Controller
{
    public function __construct(
        protected CodigoService $codigos,
    ) {}

    /**
     * Unidades con las que se controla el stock (conteo de piezas) y
     * las que pueden actuar como empaque de almacenaje.
     *
     * @return array<string, array<int, mixed>>
     */
    private function unidadesDisponibles(): array
    {
        $todas = UnitMeasure::orderBy('name')->get(['id', 'name', 'symbol']);

        return [
            'base' => $todas->whereIn('name', ['Unidad', 'Bolsa', 'Paquete'])->values(),
            'empaque' => $todas->where('name', 'Caja')->values(),
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function reglasUnidades(): array
    {
        return [
            'unidad_base_id' => ['required', 'integer', 'exists:unit_measures,id'],
            'unidad_empaque_id' => ['nullable', 'integer', 'exists:unit_measures,id'],
            'factor_empaque' => [
                'nullable',
                'integer',
                'gt:0',
                'required_with:unidad_empaque_id',
                'lte:10000',
            ],
        ];
    }

    public function index(Request $request)
    {
        $buscar = $request->buscar;

        $variantes = ProductVariant::with([
            'product.category',
            'unitMeasure',
            'unidadBase',
            'unidadEmpaque',
        ])
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('sku', 'like', "%{$buscar}%")
                        ->orWhere('flavor', 'like', "%{$buscar}%")
                        ->orWhereHas('product', function ($pq) use ($buscar) {
                            $pq->where('name', 'like', "%{$buscar}%")
                                ->orWhere('code', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $resumen = [
            'total' => ProductVariant::count(),
            'activas' => ProductVariant::where('is_active', true)->count(),
            'inactivas' => ProductVariant::where('is_active', false)->count(),
            'stockBajo' => ProductVariant::where('is_active', true)
                ->whereRaw('(SELECT COALESCE(SUM(lotes.stock), 0) FROM lotes WHERE lotes.product_variant_id = product_variants.id) <= product_variants.minimum_stock')
                ->count(),
        ];

        return Inertia::render('Inventario/Variantes/Index', [
            'variantes' => $variantes,
            'resumen' => $resumen,
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Variantes/Create', [
            'productos' => Product::where('is_active', true)
                ->withCount('productVariants')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'unidades' => UnitMeasure::orderBy('name')->get(['id', 'name', 'symbol']),
            'unidadesBase' => $this->unidadesDisponibles()['base'],
            'unidadesEmpaque' => $this->unidadesDisponibles()['empaque'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'product_id' => ['required', 'exists:products,id'],
            'unit_measure_id' => ['required', 'exists:unit_measures,id'],
            'flavor' => ['nullable', 'string', 'max:100'],
            'packaging_type' => ['nullable', 'string', 'max:100'],
            'net_content' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ], $this->reglasUnidades()));

        $producto = Product::findOrFail($validated['product_id']);

        $validated['sku'] = $this->codigos->siguienteSku($producto);
        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
        $validated['is_active'] = true;
        $validated['unidad_empaque_id'] = $validated['unidad_empaque_id'] ?? null;
        $validated['factor_empaque'] = $validated['unidad_empaque_id']
            ? $validated['factor_empaque']
            : null;

        ProductVariant::create($validated);

        return redirect()
            ->route('variantes.index')
            ->with('success', 'Variante creada correctamente.');
    }

    public function edit(ProductVariant $variante)
    {
        return Inertia::render('Inventario/Variantes/Edit', [
            'variante' => $variante->load(['product', 'unitMeasure', 'unidadBase', 'unidadEmpaque']),
            'productos' => Product::withCount('productVariants')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'unidades' => UnitMeasure::orderBy('name')->get(['id', 'name', 'symbol']),
            'unidadesBase' => $this->unidadesDisponibles()['base'],
            'unidadesEmpaque' => $this->unidadesDisponibles()['empaque'],
        ]);
    }

    public function update(Request $request, ProductVariant $variante)
    {
        $validated = $request->validate(array_merge([
            'product_id' => ['required', 'exists:products,id'],
            'unit_measure_id' => ['required', 'exists:unit_measures,id'],
            'flavor' => ['nullable', 'string', 'max:100'],
            'packaging_type' => ['nullable', 'string', 'max:100'],
            'net_content' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ], $this->reglasUnidades()));

        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
        $validated['unidad_empaque_id'] = $validated['unidad_empaque_id'] ?? null;
        $validated['factor_empaque'] = $validated['unidad_empaque_id']
            ? $validated['factor_empaque']
            : null;

        $variante->update($validated);

        return redirect()
            ->route('variantes.index')
            ->with('success', 'Variante actualizada correctamente.');
    }

    public function destroy(ProductVariant $variante)
    {
        $variante->update(['is_active' => false]);

        return redirect()
            ->route('variantes.index')
            ->with('success', 'Variante desactivada correctamente.');
    }

    public function activar(ProductVariant $variante)
    {
        $variante->update(['is_active' => true]);

        return redirect()
            ->route('variantes.index')
            ->with('success', 'Variante activada correctamente.');
    }
}
