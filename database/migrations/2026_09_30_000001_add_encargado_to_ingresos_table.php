<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingresos', function (Blueprint $table) {
            $table->foreignId('encargado_id')
                ->nullable()
                ->after('fecha')
                ->constrained('employees')
                ->restrictOnDelete();
        });

        $this->asignarEncargado();

        // El responsable de la mercadería recibida es obligatorio.
        Schema::table('ingresos', function (Blueprint $table) {
            $table->foreignId('encargado_id')->nullable(false)->change();
        });

        Schema::table('ingresos', function (Blueprint $table) {
            $table->dropColumn('proveedor');
        });
    }

    /**
     * Los ingresos existentes heredan como encargado al empleado del
     * usuario que los registró; si ese usuario no estuviera vinculado a
     * una ficha, se toma el primer empleado del padrón.
     */
    private function asignarEncargado(): void
    {
        $porDefecto = DB::table('employees')->orderBy('id')->value('id');

        if ($porDefecto === null) {
            return;
        }

        DB::table('ingresos as i')
            ->leftJoin('users as u', 'u.id', '=', 'i.user_id')
            ->orderBy('i.id')
            ->select([
                'i.id',
                DB::raw('COALESCE(u.employee_id, '.(int) $porDefecto.') as encargado'),
            ])
            ->chunk(200, function ($filas) {
                foreach ($filas as $fila) {
                    DB::table('ingresos')
                        ->where('id', $fila->id)
                        ->update(['encargado_id' => $fila->encargado]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('ingresos', function (Blueprint $table) {
            $table->string('proveedor')->nullable()->after('fecha');
        });

        Schema::table('ingresos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('encargado_id');
        });
    }
};
