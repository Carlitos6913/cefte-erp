<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CodigoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class CodigoProductoTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    public function test_el_servicio_genera_codigos_consecutivos_sin_reinicio_anual(): void
    {
        $servicio = app(CodigoService::class);

        $this->assertSame('PROD-000001', $servicio->siguienteProducto());
        $this->assertSame('PROD-000002', $servicio->siguienteProducto());
        $this->assertSame('PROD-000003', $servicio->siguienteProducto());
    }

    public function test_la_previsualizacion_no_consume_la_secuencia(): void
    {
        $servicio = app(CodigoService::class);

        $this->assertSame('PROD-000001', $servicio->previsualizarProducto());
        $this->assertSame('PROD-000001', $servicio->previsualizarProducto());

        $this->assertSame('PROD-000001', $servicio->siguienteProducto());
        $this->assertSame('PROD-000002', $servicio->previsualizarProducto());
    }

    public function test_el_codigo_del_producto_se_asigna_al_guardar(): void
    {
        $user = $this->usuario();

        $this->actingAs($user)->post('/productos', [
            'name' => 'Jugo de mango',
        ])->assertSessionHasNoErrors();

        $this->assertSame('PROD-000001', Product::where('name', 'Jugo de mango')->first()->code);

        $this->actingAs($user)->post('/productos', [
            'name' => 'Jugo de durazno',
        ])->assertSessionHasNoErrors();

        $this->assertSame('PROD-000002', Product::where('name', 'Jugo de durazno')->first()->code);
    }

    public function test_el_codigo_enviado_por_el_usuario_se_ignora(): void
    {
        $user = $this->usuario();

        $this->actingAs($user)->post('/productos', [
            'code' => 'HACK-999',
            'name' => 'Producto seguro',
        ])->assertSessionHasNoErrors();

        $this->assertSame('PROD-000001', Product::where('name', 'Producto seguro')->first()->code);
    }

    public function test_el_codigo_no_puede_modificarse_al_editar(): void
    {
        $user = $this->usuario();

        $this->actingAs($user)->post('/productos', [
            'name' => 'Producto original',
        ])->assertSessionHasNoErrors();

        $producto = Product::where('name', 'Producto original')->first();

        $this->actingAs($user)->put('/productos/'.$producto->id, [
            'code' => 'OTRO-111',
            'name' => 'Producto renombrado',
        ])->assertSessionHasNoErrors();

        $producto->refresh();

        $this->assertSame('PROD-000001', $producto->code);
        $this->assertSame('Producto renombrado', $producto->name);
    }

    public function test_el_sku_se_genera_a_partir_del_codigo_del_producto(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();

        $this->actingAs($user)->post('/variantes', [
            'product_id' => $variante->product_id,
            'unit_measure_id' => $variante->unit_measure_id, 'unidad_base_id' => $variante->unidad_base_id,
            'unit_price' => 12,
        ])->assertSessionHasNoErrors();

        $codigoProducto = $variante->product->code;

        $nuevas = $variante->product->productVariants()
            ->where('sku', 'like', $codigoProducto.'-%')
            ->get();

        $this->assertCount(1, $nuevas);
        $this->assertSame($codigoProducto.'-2', $nuevas->first()->sku);
    }

    public function test_el_sku_incrementa_el_ordinal_por_variante_del_producto(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();
        $producto = $variante->product;

        foreach ([10, 20, 30] as $precio) {
            $this->actingAs($user)->post('/variantes', [
                'product_id' => $producto->id,
                'unit_measure_id' => $variante->unit_measure_id, 'unidad_base_id' => $variante->unidad_base_id,
                'unit_price' => $precio,
            ])->assertSessionHasNoErrors();
        }

        $generados = $producto->productVariants()
            ->where('sku', 'like', $producto->code.'-%')
            ->orderBy('id')
            ->pluck('sku')
            ->all();

        $this->assertSame([
            $producto->code.'-2',
            $producto->code.'-3',
            $producto->code.'-4',
        ], $generados);
    }

    public function test_el_sku_enviado_por_el_usuario_se_ignora(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();

        $this->actingAs($user)->post('/variantes', [
            'product_id' => $variante->product_id,
            'unit_measure_id' => $variante->unit_measure_id, 'unidad_base_id' => $variante->unidad_base_id,
            'sku' => 'HACK-SKU',
            'unit_price' => 15,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('product_variants', ['sku' => 'HACK-SKU']);
    }
}
