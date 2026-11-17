<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
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

        $empleados = Employee::with('user.roles')
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('first_name', 'like', "%{$buscar}%")
                        ->orWhere('first_surname', 'like', "%{$buscar}%")
                        ->orWhere('second_surname', 'like', "%{$buscar}%")
                        ->orWhere('identity_card', 'like', "%{$buscar}%")
                        ->orWhere('phone', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Empleados/Index', [
            'empleados' => $empleados,
            'resumen' => [
                'total' => Employee::count(),
                'activos' => Employee::where('is_active', true)->count(),
                'conUsuario' => Employee::whereHas('user')->count(),
            ],
            'filters' => [
                'buscar' => $buscar,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Empleados/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'first_surname' => ['required', 'string', 'max:100'],
            'second_surname' => ['nullable', 'string', 'max:100'],
            'identity_card' => ['required', 'string', 'max:20', 'unique:employees,identity_card'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'profession' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150', 'unique:employees,email'],
        ]);

        $validated['is_active'] = true;

        Employee::create($validated);

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Empleado registrado correctamente.');
    }

    public function edit(Employee $empleado)
    {
        return Inertia::render('Empleados/Edit', [
            'empleado' => $empleado->load('user'),
        ]);
    }

    public function update(Request $request, Employee $empleado)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'first_surname' => ['required', 'string', 'max:100'],
            'second_surname' => ['nullable', 'string', 'max:100'],
            'identity_card' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees', 'identity_card')->ignore($empleado->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'profession' => ['nullable', 'string', 'max:100'],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('employees', 'email')->ignore($empleado->id),
            ],
        ]);

        $empleado->update($validated);

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(Employee $empleado)
    {
        $empleado->update(['is_active' => false]);

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Empleado desactivado correctamente.');
    }

    public function activar(Employee $empleado)
    {
        $empleado->update(['is_active' => true]);

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Empleado activado correctamente.');
    }

    public function usuario(Employee $empleado)
    {
        if ($empleado->user) {
            return redirect()
                ->route('empleados.index')
                ->with('error', 'Este empleado ya tiene un usuario asignado.');
        }

        return Inertia::render('Empleados/Usuario', [
            'empleado' => $empleado,
            'roles' => Role::orderBy('name')->get(),
            'modulosDisponibles' => $this->modulosDisponibles,
        ]);
    }

    public function asignarUsuario(Request $request, Employee $empleado)
    {
        if ($empleado->user) {
            return redirect()
                ->route('empleados.index')
                ->with('error', 'Este empleado ya tiene un usuario asignado.');
        }

        $reglas = [
            'name' => ['nullable', 'string', 'max:100'],
            'role' => ['required', Rule::in(array_merge(
                ['__nuevo__'],
                Role::pluck('name')->toArray()
            ))],
            'nuevo_rol' => ['required_if:role,__nuevo__', 'nullable', 'string', 'max:100', 'unique:roles,name'],
            'modulos' => ['required_if:role,__nuevo__', 'nullable', 'array'],
            'modulos.*' => ['string'],
        ];

        // El correo de acceso es el registrado en la ficha del empleado
        // y no se acepta desde el formulario. Solo es editable
        // mientras el empleado no tenga un correo registrado.
        $correoDelEmpleado = $empleado->email;

        if (! $correoDelEmpleado) {
            $reglas['email'] = ['nullable', 'email', 'unique:users,email'];
        }

        $validated = $request->validate($reglas);

        if ($correoDelEmpleado) {
            $validated['email'] = $correoDelEmpleado;
        }

        $validated['employee_id'] = $empleado->id;

        $this->userService->store($validated);

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Usuario asignado al empleado correctamente.');
    }
}
