<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformesTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_secciones_de_informes_responden_para_un_administrador(): void
    {
        $this->seed();

        $admin = User::role('Administrador')->firstOrFail();

        foreach (['resumen', 'ventas', 'inventario', 'ingresos', 'cobranzas'] as $seccion) {
            $response = $this->actingAs($admin)->get(route('informes.index', [
                'seccion' => $seccion,
                'fecha_inicio' => now()->startOfMonth()->toDateString(),
                'fecha_fin' => now()->toDateString(),
            ]));

            $response->assertOk();

            $response->assertInertia(fn ($page) => $page
                ->component('Informes/Index')
                ->where('seccion', $seccion)
                ->has('resumen')
                ->has('comparativo')
                ->has('tendencia')
                ->has('periodo')
            );
        }
    }

    public function test_las_exportaciones_de_informes_generan_csv(): void
    {
        $this->seed();

        $admin = User::role('Administrador')->firstOrFail();

        foreach (['resumen', 'ventas', 'inventario', 'ingresos', 'cobranzas'] as $seccion) {
            $response = $this->actingAs($admin)->get(route('informes.exportar', [
                'seccion' => $seccion,
                'fecha_inicio' => now()->startOfMonth()->toDateString(),
                'fecha_fin' => now()->toDateString(),
            ]));

            $response->assertOk();
            $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
            $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
        }
    }

    public function test_el_periodo_invalido_se_normaliza(): void
    {
        $this->seed();

        $admin = User::role('Administrador')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('informes.index', [
            'fecha_inicio' => '2026-10-31',
            'fecha_fin' => '2026-10-01',
        ]));

        $response->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->where('periodo.inicio', '2026-10-01')
            ->where('periodo.fin', '2026-10-31')
        );
    }
}
