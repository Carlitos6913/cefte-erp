<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20);
            $table->unsignedSmallInteger('anio');
            $table->unsignedBigInteger('ultimo')->default(0);
            $table->timestamps();

            $table->unique(['codigo', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlativos');
    }
};
