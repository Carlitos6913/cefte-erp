<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class CambioContrasenaTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    public function test_el_usuario_es_redirigido_al_cambio_obligatorio(): void
    {
        $user = $this->usuario();
        $user->update(['must_change_password' => true]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('password.cambio'));
    }

    public function test_el_usuario_sin_bandera_accede_normalmente(): void
    {
        $user = $this->usuario();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk();
    }

    public function test_completar_el_cambio_limpia_la_bandera(): void
    {
        $user = $this->usuario();
        $user->update(['must_change_password' => true]);

        $response = $this->actingAs($user)->put('/password/cambio', [
            'current_password' => 'password',
            'password' => 'nueva-clave',
            'password_confirmation' => 'nueva-clave',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('nueva-clave', $user->fresh()->password));
    }

    public function test_la_contrasena_actual_es_obligatoria(): void
    {
        $user = $this->usuario();
        $user->update(['must_change_password' => true]);

        $response = $this->actingAs($user)->put('/password/cambio', [
            'current_password' => 'incorrecta',
            'password' => 'nueva-clave',
            'password_confirmation' => 'nueva-clave',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_nuevo_usuario_creado_con_contrasena_por_defecto_debe_cambiarla(): void
    {
        $user = $this->usuario();
        $employee = Employee::create([
            'first_name' => 'Ana',
            'first_surname' => 'Perez',
            'identity_card' => '1234567',
            'is_active' => true,
        ]);

        Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);

        app(UserService::class)->store([
            'employee_id' => $employee->id,
            'role' => 'Vendedor',
            'modulos' => ['ventas'],
        ]);

        $nuevo = User::where('employee_id', $employee->id)->firstOrFail();

        $this->assertTrue($nuevo->must_change_password);
        $this->assertTrue(Hash::check('1234567', $nuevo->password));
    }
}
