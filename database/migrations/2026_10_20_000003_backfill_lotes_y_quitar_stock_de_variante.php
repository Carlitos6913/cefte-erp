<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convierte el stock agregado existente en lotes y deja a la variante
 * sin columna `stock`: desde ahora el stock es siempre la suma de los
 * lotes (fuente de verdad única).
 *
 * A cada variante con stock > 0 se le crea un "lote inicial" con una
 * caducidad lejana para que el FEFO no lo priorice por error frente a
 * lotes reales que se registren después.
 */
return new class extends Migration
{
    public function up(): void
    {
        $caducidadInicial = now()->addYear()->toDateString();

        DB::table('product_variants')
            ->where('stock', '>', 0)
            ->orderBy('id')
            ->each(function ($variante) use ($caducidadInicial) {
                DB::table('lotes')->insert([
                    'product_variant_id' => $variante->id,
                    'codigo' => 'INICIAL',
                    'fecha_caducidad' => $caducidadInicial,
                    'cantidad_inicial' => $variante->stock,
                    'stock' => $variante->stock,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('stock', 10, 2)->default(0)->after('net_content');
        });

        DB::table('product_variants')->orderBy('id')->each(function ($variante) {
            $stock = (float) DB::table('lotes')
                ->where('product_variant_id', $variante->id)
                ->sum('stock');

            DB::table('product_variants')
                ->where('id', $variante->id)
                ->update(['stock' => $stock]);
        });
    }
};
