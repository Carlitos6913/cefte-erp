<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Employee - selección existente
            'employee_id' => ['nullable', 'exists:employees,id'],

            // Employee - creación nuevos
            'first_name' => ['required_without:employee_id', 'nullable', 'string', 'max:100'],
            'first_surname' => ['required_without:employee_id', 'nullable', 'string', 'max:100'],
            'second_surname' => ['nullable', 'string', 'max:100'],
            'identity_card' => ['required_without:employee_id', 'nullable', 'string', 'max:20', 'unique:employees,identity_card'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:100'],
            'email_employee' => ['nullable', 'email', 'max:150'],

            // User
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_merge(
                ['__nuevo__'],
                Role::pluck('name')->toArray()
            ))],

            // Nuevo rol
            'nuevo_rol' => ['required_if:role,__nuevo__', 'nullable', 'string', 'max:100', 'unique:roles,name'],
            'modulos' => ['required_if:role,__nuevo__', 'nullable', 'array'],
            'modulos.*' => ['string'],
        ];
    }
}
