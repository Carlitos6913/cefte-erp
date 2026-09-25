<?php

namespace Tests\Feature;

use App\Models\Ingreso;
use App\Models\MovimientoInventario;
use App\Models\Salida;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class KardexTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    public function test_el_ingreso_genera_movimiento_de_kardex(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();

        $this->actingAs($user)->post('/ingresos', [
            'fecha' => '2026-09-25',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 10, 'precio_unitario' => 4],
            ],
        ])->assertSessionHasNoErrors();

        $movimiento = MovimientoInventario::firstOrFail();

        $this->assertSame('10.00', (string) $movimiento->entrada);
        $this->assertSame('0.00', (string) $movimiento->salida);
        $this->assertSame('0.00', (string) $movimiento->stock_anterior);
        $this->assertSame('10.00', (string) $movimiento->stock_nuevo);
        $this->assertSame('ingreso', $movimiento->referencia);
        $this->assertSame($variante->id, $movimiento->product_variant_id);
    }

    public function test_la_salida_genera_movimiento_de_kardex(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(10);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 4],
            ],
        ])->assertSessionHasNoErrors();

        $movimiento = MovimientoInventario::firstOrFail();

        $this->assertSame('4.00', (string) $movimiento->salida);
        $this->assertSame('10.00', (string) $movimiento->stock_anterior);
        $this->assertSame('6.00', (string) $movimiento->stock_nuevo);
        $this->assertSame('salida', $movimiento->referencia);
    }

    public function test_anular_ingreso_revierte_stock_y_registra_en_kardex(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();

        $this->actingAs($user)->post('/ingresos', [
            'fecha' => '2026-09-25',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 10, 'precio_unitario' => 4],
            ],
        ])->assertSessionHasNoErrors();

        $ingreso = Ingreso::firstOrFail();

        $this->actingAs($user)->delete('/ingresos/'.$ingreso->id);

        $this->assertSame(0.0, (float) $variante->fresh()->stock);
        $this->assertSame('anulado', $ingreso->fresh()->estado);

        $movimientos = MovimientoInventario::orderBy('id')->get();

        $this->assertCount(2, $movimientos);
        $this->assertSame('10.00', (string) $movimientos[0]->entrada);
        $this->assertSame('10.00', (string) $movimientos[1]->salida);
        $this->assertSame('0.00', (string) $movimientos[1]->stock_nuevo);
    }

    public function test_anular_salida_devuelve_stock_y_registra_en_kardex(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(10);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'dano',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 4],
            ],
        ])->assertSessionHasNoErrors();

        $salida = Salida::firstOrFail();

        $this->actingAs($user)->delete('/salidas/'.$salida->id);

        $this->assertSame(10.0, (float) $variante->fresh()->stock);

        $movimientos = MovimientoInventario::orderBy('id')->get();

        $this->assertCount(2, $movimientos);
        $this->assertSame('4.00', (string) $movimientos[0]->salida);
        $this->assertSame('4.00', (string) $movimientos[1]->entrada);
        $this->assertSame('10.00', (string) $movimientos[1]->stock_nuevo);
    }

    public function test_la_salida_no_puede_dejar_stock_negativo(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(2);

        $response = $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'dano',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('detalles');
        $this->assertSame(2.0, (float) $variante->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::count());
    }
}
