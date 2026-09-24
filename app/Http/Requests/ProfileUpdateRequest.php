<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'employee' => ['nullable', 'array'],
            'employee.first_name' => ['nullable', 'string', 'max:100'],
            'employee.first_surname' => ['nullable', 'string', 'max:100'],
            'employee.second_surname' => ['nullable', 'string', 'max:100'],
            'employee.identity_card' => ['nullable', 'string', 'max:20'],
            'employee.phone' => ['nullable', 'string', 'max:20'],
            'employee.address' => ['nullable', 'string', 'max:255'],
            'employee.profession' => ['nullable', 'string', 'max:100'],
        ];
    }
}
