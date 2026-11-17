<?php

namespace Tests\Feature;

use App\Models\Ingreso;
use App\Models\Salida;
use App\Models\Venta;
use App\Services\NumeradorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaCatalogos;
use Tests\TestCase;

class CorrelativosTest extends TestCase
{
    use CreaCatalogos;
    use RefreshDatabase;

    public function test_el_servicio_genera_correlativos_secuenciales_por_anio(): void
    {
        $servicio = app(NumeradorService::class);

        $anio = now()->format('Y');

        $this->assertSame("ING-{$anio}-000001", $servicio->siguiente('ING'));
        $this->assertSame("ING-{$anio}-000002", $servicio->siguiente('ING'));
        $this->assertSame("SAL-{$anio}-000001", $servicio->siguiente('SAL'));
        $this->assertSame("VEN-{$anio}-000001", $servicio->siguiente('VEN'));
        $this->assertSame('ING-2025-000001', $servicio->siguiente('ING', 2025));
    }

    public function test_los_documentos_generados_por_http_son_secuenciales(): void
    {
        $user = $this->usuario();
        $variante = $this->crearVariante();
        $encargado = $this->crearEncargado();

        $this->actingAs($user)->post('/ingresos', [
            'fecha' => '2026-09-25',
            'encargado_id' => $encargado->id,
            'detalles' => [
                ['product_variant_id' => $variante->id, 'lote' => 'L-1', 'fecha_caducidad' => '2027-01-01', 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 1, 'precio_unitario' => 4],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/ingresos', [
            'fecha' => '2026-09-26',
            'encargado_id' => $encargado->id,
            'detalles' => [
                ['product_variant_id' => $variante->id, 'lote' => 'L-2', 'fecha_caducidad' => '2027-02-01', 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 1, 'precio_unitario' => 4],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/salidas', [
            'fecha' => '2026-09-27',
            'motivo' => 'venta',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'cantidad' => 1],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post('/ventas', [
            'fecha' => '2026-09-28',
            'pago_tipo' => 'contado',
            'detalles' => [
                ['product_variant_id' => $variante->id, 'unidad_id' => $variante->unidad_base_id, 'cantidad' => 1, 'precio_unitario' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $anio = now()->format('Y');

        $this->assertSame("ING-{$anio}-000001", Ingreso::orderBy('id')->first()->numero);
        $this->assertSame("ING-{$anio}-000002", Ingreso::orderBy('id')->get()[1]->numero);
        $this->assertSame("SAL-{$anio}-000001", Salida::first()->numero);
        $this->assertSame("VEN-{$anio}-000001", Venta::first()->numero);
    }
}
