<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private array $modulosDisponibles = [
        'dashboard' => 'Dashboard',
        'empleados' => 'Empleados',
        'usuarios' => 'Usuarios',
        'categorias' => 'Categorías',
        'unidades' => 'Unidades de medida',
        'productos' => 'Productos',
        'variantes' => 'Variantes de producto',
        'ingresos' => 'Ingresos',
        'clientes' => 'Clientes',
        'ventas' => 'Ventas',
        'pagos' => 'Pagos',
        'informes' => 'Informes',
    ];

    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                $raw = DB::table('roles')->where('id', $role->id)->value('modulos');

                $role->modulos = $raw
                    ? (is_array($raw) ? $raw : json_decode($raw, true) ?? [])
                    : [];

                $role->accesoTotal = empty($role->modulos);

                return $role;
            });

        return Inertia::render('Administracion/Roles/Index', [
            'roles' => $roles,
            'modulosDisponibles' => $this->modulosDisponibles,
        ]);
    }

    public function edit(Role $role)
    {
        $modulos = [];
        $raw = DB::table('roles')->where('id', $role->id)->value('modulos');
        if ($raw) {
            $modulos = is_array($raw) ? $raw : json_decode($raw, true) ?? [];
        }

        return Inertia::render('Administracion/Roles/Edit', [
            'role' => $role,
            'modulos' => $modulos,
            'modulosDisponibles' => $this->modulosDisponibles,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'modulos' => ['nullable', 'array'],
            'modulos.*' => ['string'],
        ]);

        $role->update(['name' => $validated['name']]);

        $modulos = $validated['modulos'] ?? [];

        DB::table('roles')
            ->where('id', $role->id)
            ->update(['modulos' => empty($modulos) ? null : json_encode($modulos)]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Administrador') {
            return redirect()
                ->route('roles.index')
                ->with('error', 'No se puede eliminar el rol Administrador.');
        }

        if (DB::table('model_has_roles')->where('role_id', $role->id)->exists()) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}
