<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('tahun')->nullable();
            $table->string('periode', 50)->nullable();
            $table->longText('uraian_penilaian')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('predikat', 50)->nullable();
            $table->string('pejabat_penilai', 150)->nullable();
            $table->longText('catatan')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_performances');
    }
};