<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Habilita el ingreso y la venta por unidades o por cajas.
 *
 * El stock se controla siempre en la UNIDAD BASE de la variante
 * (por defecto "Unidad": una botella, una lata, una bolsa, un
 * paquete). La caja es solo una medida de almacenaje y agrupamiento:
 * cada variante declara cuántas unidades base trae su empaque en
 * `factor_empaque`.
 *
 * En los documentos se guarda la cantidad tal como la capturó el
 * usuario (`cantidad_original` + `unidad_id`) y, en `cantidad`, la
 * cantidad ya normalizada a unidad base, que es la que alimenta el
 * stock y el kardex.
 */
return new class extends Migration
{
    public function up(): void
    {
        $unidadBase = DB::table('unit_measures')->where('name', 'Unidad')->value('id');
        $caja = DB::table('unit_measures')->where('name', 'Caja')->value('id');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('unidad_base_id')
                ->nullable()
                ->after('unit_measure_id')
                ->constrained('unit_measures')
                ->restrictOnDelete();

            $table->foreignId('unidad_empaque_id')
                ->nullable()
                ->after('unidad_base_id')
                ->constrained('unit_measures')
                ->restrictOnDelete();

            // Cuántas unidades base trae un empaque. Nulo = sin empaque.
            $table->decimal('factor_empaque', 10, 2)
                ->nullable()
                ->after('unidad_empaque_id');

            $table->index(['unidad_base_id', 'unidad_empaque_id'], 'var_unidades_idx');
        });

        /*
         * Las variantes existentes ya contaban "cantidad" como piezas de
         * su presentación, así que su unidad base es "Unidad" y el stock
         * actual no se altera. Cuando la variante ya venia expresada en
         * cajas, su contenido pasa a ser el factor de empaque.
         */
        DB::table('product_variants')
            ->whereNull('unidad_base_id')
            ->update(['unidad_base_id' => $unidadBase]);

        DB::table('product_variants')
            ->where('unit_measure_id', $caja)
            ->whereNull('factor_empaque')
            ->update([
                'unidad_empaque_id' => $caja,
                'factor_empaque' => DB::raw('COALESCE(net_content, 1)'),
            ]);

        foreach (['ingreso_detalles', 'venta_detalles', 'salida_detalles'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('unidad_id')
                    ->nullable()
                    ->after('cantidad')
                    ->constrained('unit_measures')
                    ->nullOnDelete();

                // Cantidad tal como la capturó el usuario, antes de
                // convertirla a unidad base.
                $table->decimal('cantidad_original', 10, 2)
                    ->nullable()
                    ->after('unidad_id');
            });
        }

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            // Unidad en la que el usuario registró el movimiento.
            // `entrada` y `salida` siempre se guardan en unidad base.
            $table->foreignId('unidad_id')
                ->nullable()
                ->after('salida')
                ->constrained('unit_measures')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['unidad_id']);
            $table->dropColumn('unidad_id');
        });

        foreach (['ingreso_detalles', 'venta_detalles', 'salida_detalles'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['unidad_id']);
                $table->dropColumn(['unidad_id', 'cantidad_original']);
            });
        }

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['unidad_base_id']);
            $table->dropForeign(['unidad_empaque_id']);
            $table->dropIndex('var_unidades_idx');
            $table->dropColumn(['unidad_base_id', 'unidad_empaque_id', 'factor_empaque']);
        });
    }
};
