<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salidas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->date('fecha');
            $table->string('motivo', 50);
            $table->text('observaciones')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('estado', 20)->default('activo');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salidas');
    }
};
