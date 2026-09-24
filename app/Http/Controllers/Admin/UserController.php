<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Employee;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserService $userService;

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

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $buscar = $request->buscar;

        $usuarios = User::with('employee', 'roles')
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhereHas('employee', function ($eq) use ($buscar) {
                            $eq->where('first_name', 'like', "%{$buscar}%")
                                ->orWhere('first_surname', 'like', "%{$buscar}%")
                                ->orWhere('second_surname', 'like', "%{$buscar}%")
                                ->orWhere('identity_card', 'like', "%{$buscar}%")
                                ->orWhere('phone', 'like', "%{$buscar}%");
                        });
                });
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Administracion/Usuarios/Index', [
            'usuarios' => $usuarios,
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        $empleadosSinUsuario = Employee::whereDoesntHave('user')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return Inertia::render('Administracion/Usuarios/Create', [
            'roles' => Role::orderBy('name')->get(),
            'modulosDisponibles' => $this->modulosDisponibles,
            'empleados' => $empleadosSinUsuario,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->userService->store($request->validated());

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(User $usuario)
    {
        $empleadosSinUsuario = Employee::whereDoesntHave('user')
            ->where('is_active', true)
            ->where('id', '!=', $usuario->employee_id)
            ->orderBy('first_name')
            ->get();

        return Inertia::render('Administracion/Usuarios/Edit', [
            'usuario' => $usuario->load('employee', 'roles'),
            'roles' => Role::orderBy('name')->get(),
            'modulosDisponibles' => $this->modulosDisponibles,
            'empleados' => $empleadosSinUsuario,
        ]);
    }

    public function update(UpdateUserRequest $request, User $usuario)
    {
        $this->userService->update($usuario, $request->validated());

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $usuario)
    {
        $this->userService->destroy($usuario);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario desactivado.');
    }

    public function activar(User $usuario)
    {
        $usuario->update(['is_active' => true]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario activado.');
    }
}
