<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
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

            // Employee - actualización
            'first_name' => ['nullable', 'string', 'max:100'],
            'first_surname' => ['nullable', 'string', 'max:100'],
            'second_surname' => ['nullable', 'string', 'max:100'],
            'identity_card' => [
                'nullable', 'string', 'max:20',
                Rule::unique('employees', 'identity_card')->ignore($this->usuario?->employee_id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:100'],
            'email_employee' => ['nullable', 'email', 'max:150'],

            // User
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($this->usuario)],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(array_merge(
                ['__nuevo__'],
                Role::pluck('name')->toArray()
            ))],

            'nuevo_rol' => ['required_if:role,__nuevo__', 'nullable', 'string', 'max:100', 'unique:roles,name'],
            'modulos' => ['required_if:role,__nuevo__', 'nullable', 'array'],
            'modulos.*' => ['string'],
        ];
    }
}
