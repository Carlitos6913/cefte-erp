<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salida_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salida_id')->constrained('salidas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salida_detalles');
    }
};
