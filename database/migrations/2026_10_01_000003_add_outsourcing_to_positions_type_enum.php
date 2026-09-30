<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jenis jabatan baru: outsourcing (Cleaning Service, Security, Driver, dll).
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE positions MODIFY COLUMN `type` ENUM('struktural','fungsional','pelaksana','outsourcing') NOT NULL DEFAULT 'pelaksana'");
        } else {
            Schema::table('positions', function (Blueprint $t) {
                $t->enum('type', ['struktural', 'fungsional', 'pelaksana', 'outsourcing'])->default('pelaksana')->change();
            });
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE positions MODIFY COLUMN `type` ENUM('struktural','fungsional','pelaksana') NOT NULL DEFAULT 'pelaksana'");
        } else {
            Schema::table('positions', function (Blueprint $t) {
                $t->enum('type', ['struktural', 'fungsional', 'pelaksana'])->default('pelaksana')->change();
            });
        }
    }
};