<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Introduce el control por lotes con fecha de caducidad.
 *
 * Cada recepción de mercadería genera un lote identificable con su
 * propia fecha de caducidad. El stock se controla por lote y la
 * variante pasa a exponer su stock como la suma de sus lotes; el
 * despacho (salida/venta) se resuelve por FEFO (First Expired,
 * First Out): primero sale el lote que caduca antes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->restrictOnDelete();
            $table->string('codigo', 50);
            $table->date('fecha_caducidad');
            $table->decimal('cantidad_inicial', 10, 2)->default(0);
            $table->decimal('stock', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_variant_id', 'codigo'], 'lote_variante_codigo_unique');
            $table->index(['product_variant_id', 'fecha_caducidad'], 'lote_variante_caducidad_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
