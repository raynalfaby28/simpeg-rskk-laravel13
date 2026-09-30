<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('employees', 'employee_category_id')) {
            if (DB::connection()->getDriverName() === 'mysql') {
                // Nama constraint FK bisa `employees_employee_category_id_foreign` (Laravel)
                // atau `employees_ibfk_2` (skema awal setup.sql) — cari nama aslinya.
                $foreign = collect(DB::select(
                    'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                    ['employees', 'employee_category_id']
                ))->pluck('CONSTRAINT_NAME')->first();

                if ($foreign) {
                    DB::statement("ALTER TABLE `employees` DROP FOREIGN KEY `{$foreign}`");
                }

                $indexes = collect(DB::select(
                    'SELECT DISTINCT INDEX_NAME FROM information_schema.STATISTICS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND INDEX_NAME != ?',
                    ['employees', 'employee_category_id', 'PRIMARY']
                ))->pluck('INDEX_NAME');

                foreach ($indexes as $index) {
                    DB::statement("ALTER TABLE `employees` DROP INDEX `{$index}`");
                }

                DB::statement('ALTER TABLE `employees` DROP COLUMN `employee_category_id`');
            } else {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropColumn('employee_category_id');
                });
            }
        }

        Schema::dropIfExists('employee_categories');
    }

    public function down(): void
    {
        Schema::create('employee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('employee_category_id')->nullable()->after('blud_category_id')
                ->constrained('employee_categories')->nullOnDelete();
        });
    }
};