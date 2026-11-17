<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secuencias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->unsignedBigInteger('ultimo')->default(0);
            $table->timestamps();
        });

        $this->inicializarSecuenciaProductos();
    }

    /**
     * Continúa la numeración desde el código más alto ya existente
     * con el patrón PROD-000001, para no repetir códigos de productos
     * registrados antes de la autogeneración.
     */
    private function inicializarSecuenciaProductos(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $mayor = 0;

        foreach (DB::table('products')->pluck('code') as $code) {
            if (preg_match('/^PROD-(\d+)$/', (string) $code, $coincidencias)) {
                $mayor = max($mayor, (int) $coincidencias[1]);
            }
        }

        if ($mayor > 0) {
            DB::table('secuencias')->insert([
                'codigo' => 'PROD',
                'ultimo' => $mayor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('secuencias');
    }
};
