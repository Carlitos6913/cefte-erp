<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salida_detalles', function (Blueprint $table) {
            $table->unsignedInteger('cantidad_vendida')->default(0)->after('cantidad');
        });
    }

    public function down(): void
    {
        Schema::table('salida_detalles', function (Blueprint $table) {
            $table->dropColumn('cantidad_vendida');
        });
    }
};
