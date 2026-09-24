<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitMeasure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UnitMeasureController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;

        $unidades = UnitMeasure::query()
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                        ->orWhere('symbol', 'like', "%{$buscar}%");
                });
            })
            ->withCount('productVariants')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Inventario/UnidadesMedida/Index', [
            'unidades' => $unidades,
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/UnidadesMedida/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:unit_measures,name'],
            'symbol' => ['required', 'string', 'max:10'],
        ]);

        UnitMeasure::create($validated);

        return redirect()
            ->route('unidades.index')
            ->with('success', 'Unidad de medida creada correctamente.');
    }

    public function edit(UnitMeasure $unidad)
    {
        return Inertia::render('Inventario/UnidadesMedida/Edit', [
            'unidad' => $unidad,
        ]);
    }

    public function update(Request $request, UnitMeasure $unidad)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('unit_measures', 'name')->ignore($unidad->id),
            ],
            'symbol' => [
                'required',
                'string',
                'max:10',
            ],
        ]);

        $unidad->update($validated);

        return redirect()
            ->route('unidades.index')
            ->with('success', 'Unidad de medida actualizada correctamente.');
    }

    public function destroy(UnitMeasure $unidad)
    {
        if ($unidad->productVariants()->exists()) {
            return back()->with('error', 'No se puede eliminar una unidad en uso por variantes.');
        }

        $unidad->delete();

        return redirect()
            ->route('unidades.index')
            ->with('success', 'Unidad de medida eliminada correctamente.');
    }
}
