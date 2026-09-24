<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UnitMeasure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductVariantController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;

        $variantes = ProductVariant::with(['product.category', 'unitMeasure'])
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

        return Inertia::render('Inventario/Variantes/Index', [
            'variantes' => $variantes,
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Variantes/Create', [
            'productos' => Product::with('productVariants')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'unidades' => UnitMeasure::orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'unit_measure_id' => ['required', 'exists:unit_measures,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:product_variants,sku'],
            'flavor' => ['nullable', 'string', 'max:100'],
            'packaging_type' => ['nullable', 'string', 'max:100'],
            'net_content' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['stock'] = $validated['stock'] ?? 0;
        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;
        $validated['is_active'] = true;

        ProductVariant::create($validated);

        return redirect()
            ->route('variantes.index')
            ->with('success', 'Variante creada correctamente.');
    }

    public function edit(ProductVariant $variante)
    {
        return Inertia::render('Inventario/Variantes/Edit', [
            'variante' => $variante->load(['product', 'unitMeasure']),
            'productos' => Product::with('productVariants')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'unidades' => UnitMeasure::orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function update(Request $request, ProductVariant $variante)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'unit_measure_id' => ['required', 'exists:unit_measures,id'],
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_variants', 'sku')->ignore($variante->id),
            ],
            'flavor' => ['nullable', 'string', 'max:100'],
            'packaging_type' => ['nullable', 'string', 'max:100'],
            'net_content' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['stock'] = $validated['stock'] ?? 0;
        $validated['minimum_stock'] = $validated['minimum_stock'] ?? 0;

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
