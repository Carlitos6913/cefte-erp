<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellido_paterno')->nullable()->after('name');
            $table->string('apellido_materno')->nullable()->after('apellido_paterno');
            $table->string('ci')->nullable()->after('apellido_materno');
            $table->string('telefono')->nullable()->after('ci');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->json('modulos')->nullable()->after('guard_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['apellido_paterno', 'apellido_materno', 'ci', 'telefono']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('modulos');
        });
    }
};
