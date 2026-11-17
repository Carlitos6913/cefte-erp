<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->buscar;

        $clientes = Cliente::query()
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('ci_nit', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'resumen' => [
                'total' => Cliente::count(),
                'activos' => Cliente::where('activo', true)->count(),
                'inactivos' => Cliente::where('activo', false)->count(),
                'conEmail' => Cliente::whereNotNull('email')->where('email', '!=', '')->count(),
            ],
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Clientes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'ci_nit' => ['nullable', 'string', 'max:30', 'unique:clientes,ci_nit'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string'],
        ]);

        $validated['activo'] = true;

        Cliente::create($validated);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente,
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'ci_nit' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('clientes', 'ci_nit')->ignore($cliente->id),
            ],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string'],
        ]);

        $cliente->update($validated);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente desactivado correctamente.');
    }

    public function activar(Cliente $cliente)
    {
        $cliente->update(['activo' => true]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente activado correctamente.');
    }
}
