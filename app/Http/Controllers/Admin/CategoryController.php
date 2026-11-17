<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;

        $categorias = Category::query()
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('code', 'like', "%{$buscar}%")
                        ->orWhere('name', 'like', "%{$buscar}%")
                        ->orWhere('description', 'like', "%{$buscar}%");
                });
            })
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Inventario/Categorias/Index', [
            'categorias' => $categorias,
            'resumen' => [
                'total' => Category::count(),
                'productos' => Product::count(),
                'conProductos' => Category::whereHas('products')->count(),
                'sinProductos' => Category::doesntHave('products')->count(),
            ],
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Categorias/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:categories,code'],
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        Category::create($validated);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Category $categoria)
    {
        return Inertia::render('Inventario/Categorias/Edit', [
            'categoria' => $categoria,
        ]);
    }

    public function update(Request $request, Category $categoria)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('categories', 'code')->ignore($categoria->id),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($categoria->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $categoria->update($validated);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $categoria)
    {
        if ($categoria->products()->exists()) {
            return back()->with('error', 'No se puede eliminar una categoría con productos asociados.');
        }

        $categoria->delete();

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
