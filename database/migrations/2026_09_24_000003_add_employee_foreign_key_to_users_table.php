<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if ($this->hasForeign('users_employee_id_foreign')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true) && $this->hasForeign('users_employee_id_foreign')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
            });
        }
    }

    private function hasForeign(string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', 'users')
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
