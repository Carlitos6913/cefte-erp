<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClientesSeeder extends Seeder
{
    private array $clientes = [
        ['nombre' => 'Supermercado Lip Nicki', 'ci_nit' => '1023456789', 'telefono' => '70111222', 'email' => 'compras@lipnicki.bo', 'direccion' => 'Av. Arce, La Paz'],
        ['nombre' => 'Tienda Doña Betty', 'ci_nit' => '2034567890', 'telefono' => '71222333', 'email' => null, 'direccion' => 'Mercado Rodríguez, La Paz'],
        ['nombre' => 'Distribuidora El Alto', 'ci_nit' => '3045678901', 'telefono' => '72334444', 'email' => 'pedidos@distelalto.bo', 'direccion' => 'Calle Junín, El Alto'],
        ['nombre' => 'Kiosco Doña Rosa', 'ci_nit' => null, 'telefono' => '73445555', 'email' => null, 'direccion' => 'Villa Fatima, La Paz'],
        ['nombre' => 'Mini Market Shahol', 'ci_nit' => '4056789012', 'telefono' => '74556666', 'email' => 'shahol@correo.bo', 'direccion' => 'Av. 16 de Julio, La Paz'],
        ['nombre' => 'Restaurante Sabor Paceño', 'ci_nit' => '5067890123', 'telefono' => '75667777', 'email' => null, 'direccion' => 'Calle Colón, La Paz'],
        ['nombre' => 'Distribuidora del Sur S.R.L.', 'ci_nit' => '6078901234', 'telefono' => '76778888', 'email' => 'contacto@ddsur.bo', 'direccion' => 'Zona Sur, La Paz'],
        ['nombre' => 'Minimercado La Curva', 'ci_nit' => '7089012345', 'telefono' => '77889999', 'email' => null, 'direccion' => 'Av. 6 de Agosto, La Paz'],
    ];

    public function run(): void
    {
        foreach ($this->clientes as $cliente) {
            Cliente::firstOrCreate(
                ['ci_nit' => $cliente['ci_nit']],
                [
                    'nombre' => $cliente['nombre'],
                    'telefono' => $cliente['telefono'],
                    'email' => $cliente['email'],
                    'direccion' => $cliente['direccion'],
                    'activo' => true,
                ]
            );
        }

        $this->command?->info('Clientes creados: '.Cliente::count());
    }
}
