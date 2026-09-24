<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->restrictOnDelete();

            $table->date('fecha');
            $table->decimal('monto', 12, 2);
            $table->string('metodo', 30)->default('efectivo');
            $table->text('observaciones')->nullable();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
