<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user()->load('employee');

        $employee = $user->employee;

        return Inertia::render('Profile/Edit', [
            'userData' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
                'email_verified_at' => $user->email_verified_at,
                'rol' => $user->roles->first()?->name,
                'employee' => $employee ? [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name,
                    'first_surname' => $employee->first_surname,
                    'second_surname' => $employee->second_surname,
                    'identity_card' => $employee->identity_card,
                    'phone' => $employee->phone,
                    'address' => $employee->address,
                    'profession' => $employee->profession,
                    'email' => $employee->email,
                ] : null,
            ],
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Actualizar datos del employee si existe
        if ($user->employee && $request->has('employee')) {
            $employeeData = $request->input('employee', []);
            $user->employee->update($employeeData);
        }

        return redirect()->route('profile.edit')->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
