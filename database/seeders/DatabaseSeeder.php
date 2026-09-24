<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Administrador',
            'Gerente',
            'Almacenero',
            'Vendedor',
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate([
                'name' => $rol,
                'guard_name' => 'web',
            ]);
        }
    }
}
