<?php

namespace Tests\Feature;

use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

/**
 * El acceso por módulos se resuelve en dos capas que deben coincidir:
 * ModuloMiddleware en el servidor y Sidebar.vue en el cliente.
 *
 * Ambas lo definían como "sin módulos asignados" y lo trataban
 * como acceso total, pero el servidor acepta también un array vacío;
 * el sidebar lo leía como un rol sin permisos y ocultaba todo el
 * menú. Estos casos fijan el contrato para que el seed no lo revierta.
 */
class ModulosPorRolTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    public function test_el_rol_de_acceso_total_se_guarda_sin_modulos_y_no_como_lista_vacia(): void
    {
        $this->seed(RolesYPermisosSeeder::class);

        $administrador = Role::where('name', 'Administrador')->firstOrFail();

        $this->assertNull(
            $administrador->modulos,
            'Administrador debe quedar en null para que el sidebar muestre todos los módulos.'
        );
    }

    public function test_el_rol_gerente_ya_no_existe(): void
    {
        $this->seed(RolesYPermisosSeeder::class);

        $this->assertNull(Role::where('name', 'Gerente')->first());
    }

    public function test_los_roles_restringidos_guardan_su_lista_de_modulos(): void
    {
        $this->seed(RolesYPermisosSeeder::class);

        $almacenero = Role::where('name', 'Almacenero')->firstOrFail();
        $vendedor = Role::where('name', 'Vendedor')->firstOrFail();

        $this->assertNotEmpty(json_decode($almacenero->modulos, true));
        $this->assertContains('ingresos', json_decode($almacenero->modulos, true));

        $this->assertNotEmpty(json_decode($vendedor->modulos, true));
        $this->assertContains('ventas', json_decode($vendedor->modulos, true));
    }

    public function test_ningun_rol_queda_con_una_lista_de_modulos_vacia(): void
    {
        $this->seed(RolesYPermisosSeeder::class);

        $vacias = Role::get()
            ->filter(fn (Role $rol) => $rol->modulos !== null && json_decode($rol->modulos, true) === [])
            ->pluck('name');

        $this->assertTrue(
            $vacias->isEmpty(),
            'Un rol con lista vacía ocultaría todo el menú: '.$vacias->implode(', ')
        );
    }

    public function test_el_almacenero_no_accede_a_los_modulos_fuera_de_su_lista(): void
    {
        $this->seed(RolesYPermisosSeeder::class);

        $almacenero = $this->usuario('Almacenero');

        $this->actingAs($almacenero)->get('/ingresos')->assertOk();
        $this->actingAs($almacenero)->get('/ventas')->assertForbidden();
    }
}
