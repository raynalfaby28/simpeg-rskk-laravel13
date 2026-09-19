<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'sub_units',
            'position_types',
            'employee_types',
            'education_types',
            'diklat_types',
            'document_types',
            'award_types',
        ];

        foreach ($tables as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('code', 64)->nullable();
                $t->string('name')->unique();
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('award_types');
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('diklat_types');
        Schema::dropIfExists('education_types');
        Schema::dropIfExists('employee_types');
        Schema::dropIfExists('position_types');
        Schema::dropIfExists('sub_units');
    }
};