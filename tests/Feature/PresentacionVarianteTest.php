<?php

namespace Tests\Feature;

use App\Models\UnitMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class PresentacionVarianteTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    public function test_presentacion_combina_sabor_envase_y_contenido(): void
    {
        $variante = $this->crearVariante(5);

        $unidad = UnitMeasure::create([
            'name' => 'Mililitro',
            'symbol' => 'ml',
        ]);

        $variante->update([
            'flavor' => 'clásico',
            'packaging_type' => 'botella PET',
            'net_content' => 250,
            'unit_measure_id' => $unidad->id,
        ]);

        $variante->unsetRelation('unitMeasure');

        $this->assertSame(
            'Clásico · Botella PET 250 ml',
            $variante->presentacion
        );
    }

    public function test_selector_de_salidas_expone_presentacion_y_oculta_sin_stock(): void
    {
        $user = $this->usuario();
        $disponible = $this->crearVariante(5);
        $agotada = $this->crearVariante(0);

        $disponible->update([
            'flavor' => 'fresa',
            'packaging_type' => 'botella',
            'net_content' => 500,
        ]);

        $this->actingAs($user)
            ->get('/salidas/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Inventario/Salidas/Create')
                ->has('productos', 1)
                ->where('productos.0.id', $disponible->id)
                ->where('productos.0.presentacion', 'Fresa · Botella 500 und')
                ->where('productos.0.stock', 5)
            );

        $this->assertNotSame($disponible->id, $agotada->id);
    }

    public function test_selector_de_ventas_expone_presentacion_del_saldo_autorizado(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(5);

        $variante->update([
            'flavor' => 'chocolate',
            'packaging_type' => 'bolsa',
            'net_content' => 30,
        ]);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 2],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->get('/ventas/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Ventas/Create')
                ->has('productos', 1)
                ->where('productos.0.id', $variante->id)
                ->where('productos.0.presentacion', 'Chocolate · Bolsa 30 und')
                ->where('productos.0.saldo_autorizado', 2)
            );
    }

    public function test_ingresos_expone_los_campos_de_presentacion(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(0);

        $variante->update([
            'packaging_type' => 'frasco',
            'net_content' => 1.5,
            'flavor' => 'mango',
        ]);

        $this->actingAs($user)
            ->get('/ingresos/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Inventario/Ingresos/Create')
                ->has('productos', 1)
                ->where('productos.0.packaging_type', 'frasco')
                ->where('productos.0.net_content', '1.50')
                ->where('productos.0.presentacion', 'Mango · Frasco 1.5 und')
            );
    }
}
