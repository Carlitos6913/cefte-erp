<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('unit_measures', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('symbol', 10);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->foreignId('unit_measure_id')
                ->constrained('unit_measures')
                ->restrictOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('flavor', 100)->nullable();
            $table->string('packaging_type', 100)->nullable();
            $table->decimal('net_content', 8, 2)->nullable();
            $table->decimal('stock', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('categorias_vieja_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('vieja_id')->primary();
            $table->unsignedBigInteger('nueva_id');
            $table->timestamps();
        });

        Schema::create('productos_vieja_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('vieja_id')->primary();
            $table->unsignedBigInteger('nueva_variante_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_vieja_mapping');
        Schema::dropIfExists('productos_vieja_mapping');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('unit_measures');
        Schema::dropIfExists('categories');
    }
};
