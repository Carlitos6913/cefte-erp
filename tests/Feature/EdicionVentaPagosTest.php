<?php

namespace Tests\Feature;

use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class EdicionVentaPagosTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    private function crearVentaCreditoConPagos(int $total, int $pagos): array
    {
        $user = $this->usuario();
        $variante = $this->crearVariante(100, 10);

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-25',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 10],
            ],
        ])->assertSessionHasNoErrors();

        $cantidad = (int) ($total / 10);

        $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-26',
            'pago_tipo' => 'credito',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => $cantidad, 'precio_unitario' => 10],
            ],
        ])->assertSessionHasNoErrors();

        $venta = Venta::firstOrFail();

        Pago::create([
            'venta_id' => $venta->id,
            'fecha' => '2026-09-27',
            'monto' => $pagos,
            'metodo' => 'efectivo',
            'user_id' => $user->id,
        ]);

        $venta->update([
            'saldo_pendiente' => max(0, $venta->total - $pagos),
        ]);

        return [$user, $venta->fresh()];
    }

    public function test_no_se_puede_marcar_como_contado_con_saldo_pendiente(): void
    {
        [$user, $venta] = $this->crearVentaCreditoConPagos(50, 30);

        $this->assertSame(20.0, (float) $venta->saldo_pendiente);

        $response = $this->actingAs($user)->put('/ventas/'.$venta->id, [
            'fecha' => '2026-09-28',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $venta->detalles()->first()->product_variant_id, 'unidad_id' => $venta->detalles()->first()->unidad_id, 'cantidad' => 5, 'precio_unitario' => 10],
            ],
        ]);

        $response->assertSessionHasErrors('pago_tipo');
        $this->assertSame('credito', $venta->fresh()->pago_tipo);
        $this->assertSame(20.0, (float) $venta->fresh()->saldo_pendiente);
    }

    public function test_al_editar_credito_se_recalcula_el_saldo_con_los_pagos(): void
    {
        [$user, $venta] = $this->crearVentaCreditoConPagos(50, 30);

        $this->actingAs($user)->put('/ventas/'.$venta->id, [
            'fecha' => '2026-09-28',
            'pago_tipo' => 'credito',
            'detalles' => [
                ['product_variant_id' => $venta->detalles()->first()->product_variant_id, 'unidad_id' => $venta->detalles()->first()->unidad_id, 'cantidad' => 5, 'precio_unitario' => 12],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(60.0, (float) $venta->fresh()->total);
        $this->assertSame(30.0, (float) $venta->fresh()->saldo_pendiente);
    }

    public function test_el_total_no_puede_quedar_menor_a_lo_ya_pagado(): void
    {
        [$user, $venta] = $this->crearVentaCreditoConPagos(50, 30);

        $response = $this->actingAs($user)->put('/ventas/'.$venta->id, [
            'fecha' => '2026-09-28',
            'pago_tipo' => 'credito',
            'detalles' => [
                ['product_variant_id' => $venta->detalles()->first()->product_variant_id, 'unidad_id' => $venta->detalles()->first()->unidad_id, 'cantidad' => 2, 'precio_unitario' => 10],
            ],
        ]);

        $response->assertSessionHasErrors('total');
        $this->assertSame(50.0, (float) $venta->fresh()->total);
    }
}
