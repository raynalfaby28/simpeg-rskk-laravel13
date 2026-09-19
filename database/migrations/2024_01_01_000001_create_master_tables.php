<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master: Unit Kerja / SOPD
        Schema::create('work_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('work_units')->nullOnDelete();
            $table->timestamps();
        });

        // Master: Jabatan
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['struktural', 'fungsional', 'pelaksana'])->default('pelaksana');
            $table->string('eselon')->nullable();
            $table->timestamps();
        });

        // Master: Golongan / Pangkat
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('golongan'); // contoh: III/a
            $table->string('pangkat')->nullable(); // contoh: Penata Muda
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });

        // Master: Kategori Pegawai
        Schema::create('employee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tetap, Kontrak, PPPK, dll
            $table->timestamps();
        });

        // Master: Jenjang Pendidikan
        Schema::create('education_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // SD, SMP, SMA, D3, S1, dst
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });

        // Master: Status Kepegawaian
        Schema::create('employment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Aktif, Cuti, Nonaktif, Pensiun, dll
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_statuses');
        Schema::dropIfExists('education_levels');
        Schema::dropIfExists('employee_categories');
        Schema::dropIfExists('ranks');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('work_units');
    }
};
