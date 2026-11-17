<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class AsignarUsuarioEmpleadoTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    private function empleado(?string $correo = null): Employee
    {
        Role::firstOrCreate(['name' => 'Almacenero', 'guard_name' => 'web']);

        return Employee::create([
            'first_name' => 'Mario',
            'first_surname' => 'Pérez',
            'identity_card' => 'CI-'.Str::upper(Str::random(8)),
            'email' => $correo,
            'is_active' => true,
        ]);
    }

    public function test_el_correo_de_acceso_toma_el_de_la_ficha_del_empleado(): void
    {
        $user = $this->usuario();
        $empleado = $this->empleado('mario.perez@cefte.edu.bo');

        $this->actingAs($user)->post('/empleados/'.$empleado->id.'/usuario', [
            'name' => 'Mario Pérez',
            'role' => 'Almacenero',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'employee_id' => $empleado->id,
            'email' => 'mario.perez@cefte.edu.bo',
        ]);
    }

    public function test_el_correo_enviado_por_el_usuario_se_ignora(): void
    {
        $user = $this->usuario();
        $empleado = $this->empleado('mario.perez@cefte.edu.bo');

        $this->actingAs($user)->post('/empleados/'.$empleado->id.'/usuario', [
            'name' => 'Mario Pérez',
            'email' => 'intruso@ejemplo.com',
            'role' => 'Almacenero',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'employee_id' => $empleado->id,
            'email' => 'mario.perez@cefte.edu.bo',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'intruso@ejemplo.com']);
    }

    public function test_el_correo_registrado_no_puede_asignarse_a_otro_usuario(): void
    {
        $user = $this->usuario();
        $conCorreo = $this->empleado('mario.perez@cefte.edu.bo');

        // Se crea primero la cuenta que posee ese correo.
        $this->actingAs($user)->post('/empleados/'.$conCorreo->id.'/usuario', [
            'name' => 'Mario Pérez',
            'role' => 'Almacenero',
        ])->assertSessionHasNoErrors();

        $otro = $this->empleado();

        // Un empleado sin correo no puede tomar un correo ya en uso.
        $this->actingAs($user)->post('/empleados/'.$otro->id.'/usuario', [
            'name' => 'Otro empleado',
            'email' => 'mario.perez@cefte.edu.bo',
            'role' => 'Almacenero',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['employee_id' => $otro->id]);
    }

    public function test_si_el_empleado_no_tiene_correo_se_puede_definir_el_de_acceso(): void
    {
        $user = $this->usuario();
        $empleado = $this->empleado();

        $this->actingAs($user)->post('/empleados/'.$empleado->id.'/usuario', [
            'name' => 'Mario Pérez',
            'email' => 'mario.acceso@cefte.edu.bo',
            'role' => 'Almacenero',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'employee_id' => $empleado->id,
            'email' => 'mario.acceso@cefte.edu.bo',
        ]);
    }

    public function test_si_el_empleado_no_tiene_correo_se_usa_el_generado_por_defecto(): void
    {
        $user = $this->usuario();
        $empleado = $this->empleado();

        $this->actingAs($user)->post('/empleados/'.$empleado->id.'/usuario', [
            'name' => 'Mario Pérez',
            'role' => 'Almacenero',
        ])->assertSessionHasNoErrors();

        $creado = User::where('employee_id', $empleado->id)->first();

        $this->assertSame($empleado->identity_card.'@cefte.edu.bo', $creado->email);
    }

    public function test_no_se_puede_asignar_dos_usuarios_al_mismo_empleado(): void
    {
        $user = $this->usuario();
        $empleado = $this->empleado('mario.perez@cefte.edu.bo');

        $this->actingAs($user)->post('/empleados/'.$empleado->id.'/usuario', [
            'name' => 'Mario Pérez',
            'role' => 'Almacenero',
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/empleados/'.$empleado->id.'/usuario', [
            'name' => 'Mario Pérez',
            'role' => 'Almacenero',
        ]);

        $this->assertSame(
            1,
            User::where('employee_id', $empleado->id)->count()
        );
    }
}
