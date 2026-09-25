<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function store(array $data): User
    {
        $roleName = $data['role'];

        if ($roleName === '__nuevo__') {
            $role = Role::create([
                'name' => $data['nuevo_rol'],
                'guard_name' => 'web',
            ]);

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['modulos' => json_encode($data['modulos'] ?? [])]);

            $roleName = $role->name;
        }

        // Obtener o crear employee
        if (! empty($data['employee_id'])) {
            $employee = Employee::findOrFail($data['employee_id']);
        } else {
            $employee = Employee::create([
                'first_name' => $data['first_name'],
                'first_surname' => $data['first_surname'],
                'second_surname' => $data['second_surname'] ?? null,
                'identity_card' => $data['identity_card'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'profession' => $data['profession'] ?? null,
                'email' => $data['email_employee'] ?? null,
                'is_active' => true,
            ]);
        }

        // La contraseña por defecto es el C.I. del empleado
        $password = $data['password'] ?? $employee->identity_card;

        $userName = $data['name'] ?? trim("{$employee->first_name} {$employee->first_surname}");

        $userEmail = $data['email'] ?? $employee->email ?? $employee->identity_card.'@cefte.edu.bo';

        $user = User::create([
            'name' => $userName,
            'email' => $userEmail,
            'password' => Hash::make($password),
            'is_active' => true,
            'employee_id' => $employee->id,
            'must_change_password' => true,
        ]);

        $user->assignRole($roleName);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $roleName = $data['role'];

        if ($roleName === '__nuevo__') {
            $role = Role::create([
                'name' => $data['nuevo_rol'],
                'guard_name' => 'web',
            ]);

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['modulos' => json_encode($data['modulos'] ?? [])]);

            $roleName = $role->name;
        }

        // Actualizar employee si se proporcionan datos
        if ($user->employee && ! empty($data['employee_id'])) {
            // Si se está cambiando a otro employee existente
            if ($data['employee_id'] != $user->employee_id) {
                $newEmployee = Employee::findOrFail($data['employee_id']);
                $user->update(['employee_id' => $newEmployee->id]);
            }
        } elseif ($user->employee) {
            // Actualizar datos del employee actual
            $user->employee->update([
                'first_name' => $data['first_name'] ?? $user->employee->first_name,
                'first_surname' => $data['first_surname'] ?? $user->employee->first_surname,
                'second_surname' => $data['second_surname'] ?? $user->employee->second_surname,
                'identity_card' => $data['identity_card'] ?? $user->employee->identity_card,
                'phone' => $data['phone'] ?? $user->employee->phone,
                'address' => $data['address'] ?? $user->employee->address,
                'profession' => $data['profession'] ?? $user->employee->profession,
                'email' => $data['email_employee'] ?? $user->employee->email,
            ]);
        }

        $userName = $data['name'] ?? $user->name;
        $userEmail = $data['email'] ?? $user->email;

        $user->update([
            'name' => $userName,
            'email' => $userEmail,
        ]);

        if (! empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password']),
                'must_change_password' => true,
            ]);
        }

        $user->syncRoles([$roleName]);

        return $user;
    }

    public function destroy(User $user): void
    {
        $user->update(['is_active' => false]);
    }
}
