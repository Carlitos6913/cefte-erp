<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CodigoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        protected CodigoService $codigos,
    ) {}

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

        $resumen = [
            'total' => Product::count(),
            'activos' => Product::where('is_active', true)->count(),
            'inactivos' => Product::where('is_active', false)->count(),
            'variantes' => ProductVariant::count(),
        ];

        return Inertia::render('Inventario/Productos/Index', [
            'productos' => $productos,
            'resumen' => $resumen,
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Productos/Create', [
            'categorias' => Category::orderBy('name')->get(['id', 'code', 'name']),
            'codigoSugerido' => $this->codigos->previsualizarProducto(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['code'] = $this->codigos->siguienteProducto();
        $validated['is_active'] = true;
        $validated = $this->guardarImagen($request, $validated);

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
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $validated = $this->guardarImagen($request, $validated, $producto);

        $producto->update($validated);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    private function guardarImagen(Request $request, array $validated, ?Product $producto = null): array
    {
        if (! $request->hasFile('image')) {
            return $validated;
        }

        if ($producto?->image_path) {
            $anterior = public_path($producto->image_path);

            if (is_file($anterior)) {
                @unlink($anterior);
            }
        }

        $directorio = public_path('images/productos');

        if (! is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $archivo = $request->file('image');

        $nombre = Str::slug($validated['name'])
            .'-'.Str::lower(Str::random(8))
            .'.'.$archivo->getClientOriginalExtension();

        $archivo->move($directorio, $nombre);

        $validated['image_path'] = 'images/productos/'.$nombre;

        return $validated;
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
