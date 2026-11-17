<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IngresoEncargadoMigrationTest extends TestCase
{
    use RefreshDatabase;

    private ?int $userId = null;

    /**
     * La migración debe dejar `encargado_id` como columna obligatoria
     * con su llave foránea hacia `employees`.
     */
    public function test_encargado_es_obligatorio_y_referencia_a_empleados(): void
    {
        $this->assertTrue(Schema::hasColumn('ingresos', 'encargado_id'));
        $this->assertFalse(Schema::hasColumn('ingresos', 'proveedor'));

        $foraneas = collect(DB::select('PRAGMA foreign_key_list(ingresos)'))
            ->filter(fn ($fila) => $fila->from === 'encargado_id')
            ->map(fn ($fila) => $fila->table);

        $this->assertSame(['employees'], $foraneas->values()->all());

        $this->expectException(QueryException::class);

        DB::table('ingresos')->insert([
            'numero' => 'ING-999',
            'fecha' => now()->toDateString(),
            'total' => 0,
            'estado' => 'activo',
        ]);
    }

    /**
     * Un ingreso sin responsable no debe poder guardarse desde el modelo.
     */
    public function test_no_se_puede_crear_ingreso_sin_encargado(): void
    {
        $this->crearEmpleado();

        $this->expectException(QueryException::class);

        Ingreso::create([
            'numero' => 'ING-001',
            'fecha' => now()->toDateString(),
            'total' => 0,
            'estado' => 'activo',
            'user_id' => $this->userId,
        ]);
    }

    /**
     * La relación `encargado` debe resolverse contra el padrón de empleados.
     */
    public function test_relacion_encargado_se_resuelve(): void
    {
        $encargado = $this->crearEmpleado();

        $ingreso = Ingreso::create([
            'numero' => 'ING-002',
            'fecha' => now()->toDateString(),
            'encargado_id' => $encargado->id,
            'total' => 0,
            'estado' => 'activo',
            'user_id' => $this->userId,
        ]);

        $ingreso->load('encargado');

        $this->assertTrue($ingreso->encargado->is($encargado));
    }

    /**
     * La migración `down()` debe restaurar la columna `proveedor`
     * y eliminar la relación con `employees`.
     *
     * Se revierten, desde la más reciente: la imagen de productos, la
     * del rol Gerente, las tres de lotes, la de unidades y la del
     * encargado. Si se agrega una migración nueva, hay que ajustar el
     * número de pasos.
     */
    public function test_la_migracion_se_puede_revertir(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 7])->assertSuccessful();

        $this->assertTrue(Schema::hasColumn('ingresos', 'proveedor'));
        $this->assertFalse(Schema::hasColumn('ingresos', 'encargado_id'));
    }

    private function crearEmpleado(): Employee
    {
        $empleado = Employee::create([
            'first_name' => 'Elena',
            'first_surname' => 'Quispe',
            'identity_card' => '1234567 LP',
            'is_active' => true,
        ]);

        $usuario = User::create([
            'name' => 'Elena Quispe',
            'email' => 'elena.'.uniqid().'@cefte.edu.bo',
            'identity_card' => '1234567 LP',
            'password' => bcrypt('secreto123'),
            'must_change_password' => false,
            'is_active' => true,
            'employee_id' => $empleado->id,
        ]);

        $this->userId = $usuario->id;

        return $empleado->refresh();
    }
}
