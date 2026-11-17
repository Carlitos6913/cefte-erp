<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Ingreso;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

/**
 * El responsable de la recepción de mercadería es un empleado del
 * padrón y no puede quedar vacío. Estos casos fijan ese contrato en
 * la capa HTTP, que es la que consume el formulario de ingresos.
 */
class IngresoEncargadoTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    public function test_registrar_ingreso_exige_un_encargado_valido(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();

        $this->actingAs($user)
            ->post('/ingresos', $this->payload($variante->id))
            ->assertSessionHasErrors('encargado_id');

        $this->assertSame(0, Ingreso::count());
    }

    public function test_el_encargado_debe_existir_en_el_padron(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();

        $this->actingAs($user)
            ->post('/ingresos', $this->payload($variante->id, 9999))
            ->assertSessionHasErrors('encargado_id');

        $this->assertSame(0, Ingreso::count());
    }

    public function test_registrar_ingreso_guarda_el_encargado_y_mueve_el_stock(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();
        $encargado = $this->crearEmpleado('Rosa', 'Mamani', '1111111 LP');

        $this->actingAs($user)
            ->post('/ingresos', $this->payload($variante->id, $encargado->id))
            ->assertSessionHasNoErrors();

        $ingreso = Ingreso::sole();

        $this->assertSame($encargado->id, $ingreso->encargado_id);
        $this->assertTrue($ingreso->encargado->is($encargado));
        $this->assertSame(2.0, (float) $variante->fresh()->stock);
    }

    public function test_editar_ingreso_cambia_el_encargado(): void
    {
        $user = $this->usuario();
        $activo = $this->crearEmpleado('Rosa', 'Mamani', '1111111 LP');
        $inactivo = $this->crearEmpleado('Luis', 'Chávez', '2222222 LP');
        $inactivo->update(['is_active' => false]);

        $this->actingAs($user)
            ->get('/ingresos/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Inventario/Ingresos/Create')
                ->where('encargados', fn ($encargados) => collect($encargados)
                    ->pluck('id')
                    ->all() === [$activo->id]
                    && collect($encargados)->pluck('identity_card')->all() === ['1111111 LP'])
            );
    }

    private function payload(int $varianteId, ?int $encargadoId = null): array
    {
        $variante = ProductVariant::findOrFail($varianteId);

        $payload = [
            'fecha' => '2026-09-25',
            'observaciones' => null,
            'detalles' => [
                [
                    'product_variant_id' => $varianteId,
                    'lote' => 'L-TEST',
                    'fecha_caducidad' => '2027-01-01',
                    'unidad_id' => $variante->unidad_base_id,
                    'cantidad' => 2,
                    'precio_unitario' => 5,
                ],
            ],
        ];

        if ($encargadoId !== null) {
            $payload['encargado_id'] = $encargadoId;
        }

        return $payload;
    }

    private function crearEmpleado(string $nombre, string $apellido, string $ci): Employee
    {
        return Employee::create([
            'first_name' => $nombre,
            'first_surname' => $apellido,
            'identity_card' => $ci,
            'is_active' => true,
        ]);
    }
}
