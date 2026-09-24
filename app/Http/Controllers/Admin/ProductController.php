<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;

        $productos = Product::with(['category', 'productVariants'])
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('code', 'like', "%{$buscar}%")
                        ->orWhere('name', 'like', "%{$buscar}%");
                });
            })
            ->withCount('productVariants')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Inventario/Productos/Index', [
            'productos' => $productos,
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Productos/Create', [
            'categorias' => Category::orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:products,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        $validated['is_active'] = true;

        Product::create($validated);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    public function edit(Product $producto)
    {
        return Inertia::render('Inventario/Productos/Edit', [
            'producto' => $producto,
            'categorias' => Category::orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function update(Request $request, Product $producto)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('products', 'code')->ignore($producto->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'category_id' => [
                'nullable',
                'exists:categories,id',
            ],
        ]);

        $producto->update($validated);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $producto)
    {
        $producto->update(['is_active' => false]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto desactivado correctamente.');
    }

    public function activar(Product $producto)
    {
        $producto->update(['is_active' => true]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto activado correctamente.');
    }
}
