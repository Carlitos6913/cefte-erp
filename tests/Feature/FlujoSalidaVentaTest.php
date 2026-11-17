<?php

namespace Tests\Feature;

use App\Models\MovimientoInventario;
use App\Models\Salida;
use App\Models\SalidaDetalle;
use App\Models\Venta;
use App\Services\SalidaVentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class FlujoSalidaVentaTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    /**
     * La venta exige que exista una salida de despacho (motivo venta).
     */
    public function test_venta_sin_salida_autorizada_se_rechaza(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $response = $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-25',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 1, 'precio_unitario' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('detalles');

        $this->assertSame(0, Venta::count());
        $this->assertSame(100.0, (float) $variante->fresh()->stock);
    }

    /**
     * Una salida con motivo distinto a 'venta' no habilita la venta.
     */
    public function test_salida_de_dano_no_autoriza_venta(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'dano',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(95.0, (float) $variante->fresh()->stock);

        $response = $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-26',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 1, 'precio_unitario' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('detalles');
        $this->assertSame(0, Venta::count());
    }

    /**
     * Flujo completo: ingreso suma stock, la salida de despacho lo
     * resta, y la venta consume el saldo sin volver a tocar el stock.
     */
    public function test_flujo_completo_ingreso_salida_venta(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $this->actingAs($user)->post('/ingresos', [
            'fecha' => '2026-09-25',
            'encargado_id' => $this->crearEncargado()->id,
            'detalles' => [
                ['product_variant_id' => $variante->id, 'lote' => 'L-TEST', 'fecha_caducidad' => '2027-01-01', 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 10, 'precio_unitario' => 4],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(110.0, (float) $variante->fresh()->stock);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-26',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(105.0, (float) $variante->fresh()->stock);

        $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-27',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 2, 'precio_unitario' => 8],
            ],
        ])->assertSessionHasNoErrors();

        // El stock físico ya fue movido por la salida; la venta no lo altera.
        $this->assertSame(105.0, (float) $variante->fresh()->stock);

        $venta = Venta::firstOrFail();
        $this->assertSame('VEN-'.now()->format('Y').'-000001', $venta->numero);

        $detalleSalida = SalidaDetalle::firstOrFail();
        $this->assertSame(2, $detalleSalida->cantidad_vendida);

        $this->assertDatabaseHas('salida_detalle_venta_detalle', [
            'salida_detalle_id' => $detalleSalida->id,
            'venta_detalle_id' => $venta->detalles()->firstOrFail()->id,
            'cantidad' => 2,
        ]);

        $this->assertSame(3, app(SalidaVentaService::class)->saldoDisponible($variante->id));
    }

    /**
     * La venta no puede superar el saldo autorizado.
     */
    public function test_venta_superior_al_saldo_autorizado_se_rechaza(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 2],
            ],
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-26',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 3, 'precio_unitario' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('detalles');
        $this->assertSame(0, Venta::count());
        $this->assertSame(0, SalidaDetalle::firstOrFail()->cantidad_vendida);
    }

    /**
     * El consumo de saldo es FIFO entre varias salidas de despacho.
     */
    public function test_consumo_fifo_entre_varias_salidas(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-20',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 3],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-21',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 2],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-22',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 4, 'precio_unitario' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $detalles = SalidaDetalle::orderBy('id')->get();

        $this->assertSame(3, $detalles[0]->cantidad_vendida);
        $this->assertSame(1, $detalles[1]->cantidad_vendida);
    }

    /**
     * No se puede anular una salida con detalles ya vendidos.
     */
    public function test_no_se_anula_salida_con_ventas(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(95.0, (float) $variante->fresh()->stock);

        $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-26',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 2, 'precio_unitario' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $salida = Salida::firstOrFail();

        $response = $this->actingAs($user)->delete('/salidas/'.$salida->id);

        $response->assertSessionHas('error');
        $this->assertSame('activo', $salida->fresh()->estado);
        $this->assertSame(95.0, (float) $variante->fresh()->stock);
    }

    /**
     * Anular una venta devuelve el saldo a la salida sin tocar el stock.
     */
    public function test_anular_venta_libera_el_saldo_autorizado(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-26',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 2, 'precio_unitario' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $venta = Venta::firstOrFail();

        $this->actingAs($user)->delete('/ventas/'.$venta->id)
            ->assertRedirect(route('ventas.index'));

        $this->assertSame('anulado', $venta->fresh()->estado);
        $this->assertSame(0, SalidaDetalle::firstOrFail()->cantidad_vendida);
        $this->assertSame(5, app(SalidaVentaService::class)->saldoDisponible($variante->id));
        $this->assertSame(95.0, (float) $variante->fresh()->stock);
    }

    /**
     * Editar una venta redistribuye el saldo consumido.
     */
    public function test_editar_venta_redistribuye_el_saldo(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 10],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-26',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 2, 'precio_unitario' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $venta = Venta::firstOrFail();
        $numero = $venta->numero;

        $this->actingAs($user)->put('/ventas/'.$venta->id, [
            'fecha' => '2026-09-27',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 4, 'precio_unitario' => 6],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(6, app(SalidaVentaService::class)->saldoDisponible($variante->id));
        $this->assertSame(4, SalidaDetalle::firstOrFail()->cantidad_vendida);
        $this->assertSame($numero, $venta->fresh()->numero);
        $this->assertSame(24.0, (float) $venta->fresh()->total);
        $this->assertSame(90.0, (float) $variante->fresh()->stock);
    }

    /**
     * El kardex solo refleja los movimientos físicos (ingreso/salida).
     */
    public function test_la_venta_no_genera_movimientos_de_kardex(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-26',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 2, 'precio_unitario' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $movimientos = MovimientoInventario::get();

        $this->assertCount(1, $movimientos);
        $this->assertSame('salida', $movimientos->first()->referencia);
    }
}
