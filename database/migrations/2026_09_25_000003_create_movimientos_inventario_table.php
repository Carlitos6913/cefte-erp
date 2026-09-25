<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->restrictOnDelete();
            $table->decimal('entrada', 10, 2)->default(0);
            $table->decimal('salida', 10, 2)->default(0);
            $table->decimal('stock_anterior', 10, 2)->default(0);
            $table->decimal('stock_nuevo', 10, 2)->default(0);
            $table->string('referencia', 20);
            $table->unsignedBigInteger('referencia_id');
            $table->string('numero', 30)->nullable();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['product_variant_id', 'referencia', 'referencia_id'], 'mov_inv_ref_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
