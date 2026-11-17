<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula los detalles de documento y los movimientos de kardex con
 * el lote que los origina o consume.
 *
 * - `ingreso_detalles.lote_id`: el lote creado al recibir mercadería.
 * - `salida_detalles.lote_id`: el lote despachado (una línea por lote
 *   cuando el FEFO reparte una cantidad entre varios lotes).
 * - `movimientos_inventario.lote_id`: trazabilidad del kardex por lote.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingreso_detalles', function (Blueprint $table) {
            $table->foreignId('lote_id')
                ->nullable()
                ->after('product_variant_id')
                ->constrained('lotes')
                ->nullOnDelete();
        });

        Schema::table('salida_detalles', function (Blueprint $table) {
            $table->foreignId('lote_id')
                ->nullable()
                ->after('product_variant_id')
                ->constrained('lotes')
                ->nullOnDelete();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('lote_id')
                ->nullable()
                ->after('product_variant_id')
                ->constrained('lotes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn('lote_id');
        });

        Schema::table('salida_detalles', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn('lote_id');
        });

        Schema::table('ingreso_detalles', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn('lote_id');
        });
    }
};
