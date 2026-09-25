<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salida_detalle_venta_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salida_detalle_id')
                ->constrained('salida_detalles')
                ->cascadeOnDelete();
            $table->foreignId('venta_detalle_id')
                ->constrained('venta_detalles')
                ->cascadeOnDelete();
            $table->unsignedInteger('cantidad');
            $table->timestamps();

            $table->index(['salida_detalle_id', 'venta_detalle_id'], 'sdvd_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salida_detalle_venta_detalle');
    }
};
