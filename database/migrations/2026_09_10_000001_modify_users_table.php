<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phase 1: Agregar employee_id si no existe
        $hasEmployeeId = Schema::hasColumn('users', 'employee_id');
        if (! $hasEmployeeId) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('employee_id')->nullable()->after('id')->unique();
            });
        }

        // Phase 2: Migrar usuarios que NO tienen employee_id aún
        $users = DB::table('users')
            ->whereNull('employee_id')
            ->where(function ($q) {
                $q->whereNotNull('ci')
                    ->orWhereNotNull('apellido_paterno')
                    ->orWhereNotNull('apellido_materno');
            })
            ->get();

        foreach ($users as $user) {
            $identityCard = $user->ci ?? ('TEMP-'.$user->id);

            // Verificar si ya existe un employee con ese CI
            $existing = DB::table('employees')
                ->where('identity_card', $identityCard)
                ->first();

            if ($existing) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['employee_id' => $existing->id]);
            } else {
                $employeeId = DB::table('employees')->insertGetId([
                    'first_name' => $user->name,
                    'first_surname' => $user->apellido_paterno ?? '',
                    'second_surname' => $user->apellido_materno ?? null,
                    'identity_card' => $identityCard,
                    'phone' => $user->telefono ?? null,
                    'address' => null,
                    'profession' => null,
                    'email' => $user->email,
                    'is_active' => $user->activo ?? true,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['employee_id' => $employeeId]);
            }
        }

        // Phase 3: Renombrar activo → is_active
        $hasActivo = Schema::hasColumn('users', 'activo');
        $hasIsActive = Schema::hasColumn('users', 'is_active');
        if ($hasActivo && ! $hasIsActive) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('activo', 'is_active');
            });
        }

        // Phase 4: Eliminar columnas que migraron a employees
        $columnsToDrop = [];
        foreach (['apellido_paterno', 'apellido_materno', 'ci', 'telefono', 'direccion', 'deleted_at'] as $col) {
            if (Schema::hasColumn('users', $col)) {
                $columnsToDrop[] = $col;
            }
        }
        if (! empty($columnsToDrop)) {
            Schema::table('users', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    public function down(): void
    {
        // Restaurar columnas eliminadas
        if (! Schema::hasColumn('users', 'apellido_paterno')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('apellido_paterno')->nullable()->after('name');
                $table->string('apellido_materno')->nullable()->after('apellido_paterno');
                $table->string('ci')->nullable()->after('apellido_materno');
                $table->string('telefono')->nullable()->after('ci');
                $table->string('direccion')->nullable()->after('telefono');
                $table->timestamp('deleted_at')->nullable()->after('remember_token');
            });
        }

        // Restaurar datos desde employees
        $users = DB::table('users')->whereNotNull('employee_id')->get();
        foreach ($users as $user) {
            $employee = DB::table('employees')->where('id', $user->employee_id)->first();
            if ($employee) {
                DB::table('users')->where('id', $user->id)->update([
                    'name' => $employee->first_name,
                    'apellido_paterno' => $employee->first_surname,
                    'apellido_materno' => $employee->second_surname,
                    'ci' => $employee->identity_card,
                    'telefono' => $employee->phone,
                    'direccion' => $employee->address,
                    'is_active' => $employee->is_active,
                ]);
            }
        }

        // Renombrar is_active → activo
        if (Schema::hasColumn('users', 'is_active') && ! Schema::hasColumn('users', 'activo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('is_active', 'activo');
            });
        }

        // Eliminar employee_id
        if (Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }
    }
};
