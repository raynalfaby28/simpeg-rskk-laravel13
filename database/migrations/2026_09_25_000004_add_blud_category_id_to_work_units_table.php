<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_units', function (Blueprint $table) {
            $table->foreignId('blud_category_id')
                ->nullable()
                ->after('name')
                ->constrained('blud_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_units', function (Blueprint $table) {
            $table->dropForeign(['blud_category_id']);
            $table->dropColumn('blud_category_id');
        });
    }
};