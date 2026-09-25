<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * En MySQL la migración 2026_09_24_000002 ya renombró la columna
 * producto_id a product_variant_id en las tablas de detalles.
 * En otras bases (SQLite de pruebas) la columna sigue llamándose
 * producto_id; esta migración la normaliza para todos los drivers.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['ingreso_detalles', 'salida_detalles', 'venta_detalles'] as $tabla) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'producto_id')) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['producto_id']);
            });

            Schema::table($tabla, function (Blueprint $table) {
                $table->renameColumn('producto_id', 'product_variant_id');
            });

            if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->foreign('product_variant_id')
                        ->references('id')
                        ->on('product_variants')
                        ->restrictOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['ingreso_detalles', 'salida_detalles', 'venta_detalles'] as $tabla) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, 'product_variant_id')) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) {
                $table->renameColumn('product_variant_id', 'producto_id');
            });
        }
    }
};
