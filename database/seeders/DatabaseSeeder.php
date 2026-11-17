<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesYPermisosSeeder::class,
            UnidadesMedidaSeeder::class,
            EmpleadosYUsuariosSeeder::class,
            CatalogoSeeder::class,
            IngresosSeeder::class,
            ClientesSeeder::class,
            SalidasVentasPagosSeeder::class,
        ]);
    }
}
