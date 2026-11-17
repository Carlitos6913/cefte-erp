<?php

namespace Database\Seeders;

use App\Models\UnitMeasure;
use Illuminate\Database\Seeder;

class UnidadesMedidaSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['name' => 'Unidad', 'symbol' => 'und'],
            ['name' => 'Litro', 'symbol' => 'l'],
            ['name' => 'Mililitro', 'symbol' => 'ml'],
            ['name' => 'Kilogramo', 'symbol' => 'kg'],
            ['name' => 'Gramo', 'symbol' => 'g'],
            ['name' => 'Caja', 'symbol' => 'cj'],
            ['name' => 'Paquete', 'symbol' => 'pq'],
            ['name' => 'Bolsa', 'symbol' => 'bol'],
        ];

        foreach ($unidades as $unidad) {
            UnitMeasure::firstOrCreate(['name' => $unidad['name']], [
                'symbol' => $unidad['symbol'],
            ]);
        }
    }
}
