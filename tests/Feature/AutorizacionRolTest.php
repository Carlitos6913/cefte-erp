<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class AutorizacionRolTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    private function crearVenta(User $user): Venta
    {
        return Venta::create([
            'numero' => 'VEN-TEST-000001',
            'fecha' => '2026-09-25',
            'cliente' => 'Cliente X',
            'total' => 10,
            'estado' => 'activo',
            'pago_tipo' => 'contado',
            'saldo_pendiente' => 0,
            'user_id' => $user->id,
        ]);
    }

    public function test_un_vendedor_no_puede_anular_ventas(): void
    {
        $admin = $this->usuario('Administrador');
        $vendedor = $this->usuario('Vendedor');

        $venta = $this->crearVenta($admin);

        $response = $this->actingAs($vendedor)->delete('/ventas/'.$venta->id);

        $response->assertForbidden();
        $this->assertSame('activo', $venta->fresh()->estado);
    }

    public function test_un_administrador_puede_anular_ventas(): void
    {
        $admin = $this->usuario('Administrador');

        $venta = $this->crearVenta($admin);

        $this->actingAs($admin)->delete('/ventas/'.$venta->id)
            ->assertRedirect(route('ventas.index'));

        $this->assertSame('anulado', $venta->fresh()->estado);
    }

    public function test_un_vendedor_no_puede_desactivar_usuarios(): void
    {
        $admin = $this->usuario('Administrador');
        $vendedor = $this->usuario('Vendedor');
        $objetivo = $this->usuario('Vendedor');

        $response = $this->actingAs($vendedor)->delete('/usuarios/'.$objetivo->id);

        $response->assertForbidden();
        $this->assertTrue($objetivo->fresh()->is_active);
    }

    public function test_un_administrador_puede_desactivar_usuarios(): void
    {
        $admin = $this->usuario('Administrador');
        $objetivo = $this->usuario('Vendedor');

        $this->actingAs($admin)->delete('/usuarios/'.$objetivo->id)
            ->assertRedirect(route('usuarios.index'));

        $this->assertFalse($objetivo->fresh()->is_active);
    }

    public function test_el_almacenero_no_puede_eliminar_empleados(): void
    {
        $almacenero = $this->usuario('Almacenero');
        $empleado = Employee::create([
            'first_name' => 'Juan',
            'first_surname' => 'Choque',
            'identity_card' => '9876543',
            'is_active' => true,
        ]);

        $response = $this->actingAs($almacenero)->delete('/empleados/'.$empleado->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('employees', ['id' => $empleado->id]);
    }
}
