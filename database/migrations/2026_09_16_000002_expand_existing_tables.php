<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori jenis pendidikan: formal / non_formal
        Schema::table('employee_educations', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('education_level_id');
        });

        // Kategori jenis diklat: struktural / fungsional / teknis / profesi / seminar
        Schema::table('employee_trainings', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('jenis_pelatihan');
        });

        // Keluarga: tambah "saudara"
        Schema::table('employee_families', function (Blueprint $table) {
            $table->enum('type', ['pasangan', 'anak', 'orang_tua', 'saudara'])->nullable(false)->change();
        });

        // Dokumen: kelompok arsip (Dokumen Pribadi, Akademik, Kepegawaian, Diklat, dll)
        Schema::table('documents', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('jenis_dokumen');
        });

        // Data pribadi tambahan ("selayaknya data pribadi")
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nama_ibu_kandung')->nullable()->after('nama_panggilan');
            $table->string('no_akta_kelahiran')->nullable()->after('nama_ibu_kandung');
            $table->string('no_buku_nikah')->nullable()->after('no_akta_kelahiran');
            $table->string('no_akta_cerai')->nullable()->after('no_buku_nikah');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['nama_ibu_kandung', 'no_akta_kelahiran', 'no_buku_nikah', 'no_akta_cerai']);
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
        Schema::table('employee_families', function (Blueprint $table) {
            $table->enum('type', ['pasangan', 'anak', 'orang_tua'])->nullable(false)->change();
        });
        Schema::table('employee_trainings', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
        Schema::table('employee_educations', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};