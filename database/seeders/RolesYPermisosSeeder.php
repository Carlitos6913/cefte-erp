<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesYPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Administrador' => [],
            'Almacenero' => [
                'categorias', 'unidades', 'productos', 'variantes',
                'ingresos', 'salidas', 'informes',
            ],
            'Vendedor' => [
                'productos', 'variantes', 'clientes', 'ventas', 'pagos',
            ],
        ];

        foreach ($roles as $nombre => $modulos) {
            $rol = Role::firstOrCreate([
                'name' => $nombre,
                'guard_name' => 'web',
            ]);

            // Un rol sin modulos asignados tiene acceso total. Debe
            // quedar en null y no como lista vacia: ModuloMiddleware
            // interpreta ambos como acceso total, pero el Sidebar
            // trata una lista vacia como un rol sin permisos y oculta
            // todos los modulos del menu.
            if ($modulos === []) {
                if ($rol->modulos !== null) {
                    $rol->forceFill(['modulos' => null])->save();
                }

                continue;
            }

            $rol->forceFill(['modulos' => json_encode($modulos)])->save();
        }
    }
}
