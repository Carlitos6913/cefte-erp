<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Cliente;
use App\Models\Employee;
use App\Models\Ingreso;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Salida;
use App\Models\UnitMeasure;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaginasPrincipalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_paginas_principales_responden_para_un_administrador(): void
    {
        $this->seed();

        $admin = User::role('Administrador')->firstOrFail();
        $role = Role::where('name', 'Administrador')->firstOrFail();

        $ingreso = Ingreso::first();
        $salida = Salida::first();
        $venta = Venta::first();

        $rutas = [
            '/dashboard',
            '/empleados',
            '/empleados/create',
            '/usuarios',
            '/usuarios/create',
            '/roles',
            '/roles/'.$role->id.'/edit',
            '/categorias',
            '/categorias/create',
            '/unidades',
            '/unidades/create',
            '/productos',
            '/productos/create',
            '/variantes',
            '/variantes/create',
            '/ingresos',
            '/ingresos/create',
            '/salidas',
            '/salidas/create',
            '/clientes',
            '/clientes/create',
            '/ventas',
            '/ventas/create',
            '/pagos',
            '/informes',
        ];

        $empleado = Employee::first();
        $empleadoSinUsuario = Employee::doesntHave('user')->first();
        $cliente = Cliente::first();
        $categoria = Category::first();
        $unidad = UnitMeasure::first();
        $producto = Product::first();
        $variante = ProductVariant::first();
        $ingresoActivo = Ingreso::where('estado', 'activo')->first();
        $salidaActiva = Salida::where('estado', 'activo')
            ->whereDoesntHave('detalles', fn ($query) => $query->where('cantidad_vendida', '>', 0))
            ->first();
        $ventaActiva = Venta::where('estado', 'activo')->first();

        if ($empleado) {
            $rutas[] = '/empleados/'.$empleado->id.'/edit';
        }

        if ($empleadoSinUsuario) {
            $rutas[] = '/empleados/'.$empleadoSinUsuario->id.'/usuario';
        }

        if ($cliente) {
            $rutas[] = '/clientes/'.$cliente->id.'/edit';
        }

        if ($categoria) {
            $rutas[] = '/categorias/'.$categoria->id.'/edit';
        }

        if ($unidad) {
            $rutas[] = '/unidades/'.$unidad->id.'/edit';
        }

        if ($producto) {
            $rutas[] = '/productos/'.$producto->id.'/edit';
        }

        if ($variante) {
            $rutas[] = '/variantes/'.$variante->id.'/edit';
        }

        if ($ingreso) {
            $rutas[] = '/ingresos/'.$ingreso->id;
        }

        if ($ingresoActivo) {
            $rutas[] = '/ingresos/'.$ingresoActivo->id.'/edit';
        }

        if ($salida) {
            $rutas[] = '/salidas/'.$salida->id;
        }

        if ($salidaActiva) {
            $rutas[] = '/salidas/'.$salidaActiva->id.'/edit';
        }

        if ($venta) {
            $rutas[] = '/ventas/'.$venta->id;
            $rutas[] = '/pagos/'.$venta->id;

            $conDeuda = Venta::where('saldo_pendiente', '>', 0)->first();

            if ($conDeuda) {
                $rutas[] = '/pagos/create?venta_id='.$conDeuda->id;
            }
        }

        if ($ventaActiva) {
            $rutas[] = '/ventas/'.$ventaActiva->id.'/edit';
        }

        foreach ($rutas as $ruta) {
            $response = $this->actingAs($admin)->get($ruta);

            $this->assertSame(200, $response->status(), "GET {$ruta} respondió {$response->status()}.");
        }
    }

    public function test_las_paginas_de_autenticacion_responden(): void
    {
        $this->seed();

        $rutas = [
            '/login',
            '/forgot-password',
            '/reset-password/token-de-prueba?email=usuario%40cefte.test',
        ];

        foreach ($rutas as $ruta) {
            $response = $this->get($ruta);

            $this->assertSame(200, $response->status(), "GET {$ruta} respondió {$response->status()}.");
        }
    }
}
