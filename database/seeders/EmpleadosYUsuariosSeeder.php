<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Seeder;

class EmpleadosYUsuariosSeeder extends Seeder
{
    /**
     * Ocho empleados del Instituto. Cuatro reciben cuenta de
     * acceso; los cuatro restantes quedan pendientes de
     * asignación para ejercitar ese flujo.
     */
    private array $empleados = [
        ['nombres' => 'Carlos', 'apellidos' => 'Muiba Zambrana', 'ci' => '1234567 LP', 'telefono' => '70112233', 'profesion' => 'Ingeniero de Sistemas', 'email' => 'carlos.muiba@cefte.edu.bo', 'rol' => 'Administrador', 'activo' => true],
        ['nombres' => 'Jhessica', 'apellidos' => 'Mamani Lima', 'ci' => '2345678 LP', 'telefono' => '71223344', 'profesion' => 'Contadora', 'email' => 'jhessica.mamani@cefte.edu.bo', 'rol' => null, 'activo' => true],
        ['nombres' => 'Renzo', 'apellidos' => 'Quispe Huamán', 'ci' => '3456789 LP', 'telefono' => '72334455', 'profesion' => 'Almacenero', 'email' => 'renzo.quispe@cefte.edu.bo', 'rol' => 'Almacenero', 'activo' => true],
        ['nombres' => 'Andrea', 'apellidos' => 'FloresCondori', 'ci' => '4567890 LP', 'telefono' => '73445566', 'profesion' => 'Vendedora', 'email' => 'andrea.flores@cefte.edu.bo', 'rol' => 'Vendedor', 'activo' => true],
        ['nombres' => 'Luis', 'apellidos' => 'Apaza Choque', 'ci' => '5678901 LP', 'telefono' => '74556677', 'profesion' => 'Vendedor', 'email' => 'luis.apaza@cefte.edu.bo', 'rol' => 'Vendedor', 'activo' => true],
        ['nombres' => 'Marisol', 'apellidos' => 'Ccahuana Rojas', 'ci' => '6789012 LP', 'telefono' => '75667788', 'profesion' => 'Almacenera', 'email' => 'marisol.ccahuana@cefte.edu.bo', 'rol' => null, 'activo' => true],
        ['nombres' => 'Iván', 'apellidos' => 'Ttito Mamani', 'ci' => '7890123 LP', 'telefono' => '76778899', 'profesion' => 'Supervisor de producción', 'email' => 'ivan.ttito@cefte.edu.bo', 'rol' => null, 'activo' => true],
        ['nombres' => 'Paola', 'apellidos' => 'Ynquipa Nuñez', 'ci' => '8901234 LP', 'telefono' => '77889900', 'profesion' => 'Analista de calidad', 'email' => 'paola.ynquipa@cefte.edu.bo', 'rol' => null, 'activo' => false],
    ];

    public function run(UserService $usuarios): void
    {
        foreach ($this->empleados as $datos) {
            $partes = explode(' ', $datos['nombres'], 2);

            $empleado = Employee::create([
                'first_name' => $partes[0],
                'first_surname' => $partes[1] ?? $partes[0],
                'second_surname' => null,
                'identity_card' => $datos['ci'],
                'phone' => $datos['telefono'],
                'address' => 'Av. circumferential, La Paz',
                'profession' => $datos['profesion'],
                'email' => $datos['email'],
                'is_active' => $datos['activo'],
            ]);

            if ($datos['rol'] === null) {
                continue;
            }

            $usuarios->store([
                'employee_id' => $empleado->id,
                'role' => $datos['rol'],
                'name' => $datos['nombres'].' '.$datos['apellidos'],
                'email' => $datos['email'],
            ]);
        }

        $this->command?->info('Empleados creados: '.Employee::count());
        $this->command?->info('Usuarios creados: '.User::count());
    }
}
