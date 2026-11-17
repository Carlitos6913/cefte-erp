<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $gerente = Role::where('name', 'Gerente')
            ->where('guard_name', 'web')
            ->first();

        if (! $gerente) {
            return;
        }

        $administrador = Role::where('name', 'Administrador')
            ->where('guard_name', 'web')
            ->first();

        foreach ($gerente->users as $usuario) {
            if ($administrador) {
                $usuario->syncRoles([$administrador]);
            } else {
                $usuario->syncRoles([]);
            }
        }

        $gerente->delete();
    }

    public function down(): void
    {
        Role::firstOrCreate([
            'name' => 'Gerente',
            'guard_name' => 'web',
        ]);
    }
};
