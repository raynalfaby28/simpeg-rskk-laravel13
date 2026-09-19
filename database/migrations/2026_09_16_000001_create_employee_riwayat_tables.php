<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat Bahasa
        Schema::create('employee_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('nama_bahasa');
            $table->string('tingkat')->nullable(); // Pemula / Menengah / Mahir / Fasih
            $table->string('kemampuan')->nullable(); // Lisan & Tulisan / Lisan / Tulisan
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Riwayat Cuti
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_cuti');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedSmallInteger('jumlah_hari')->default(0);
            $table->string('no_sk')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_sk_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Inaktif (nonaktif / pensiun sementara, dll)
        Schema::create('employee_inactive_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('status')->nullable(); // Nonaktif / Pensiun / dll
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('alasan')->nullable();
            $table->string('no_sk')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Kedudukan Hukum
        Schema::create('employee_legal_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // Tersangka / Terdakwa / Terpidana / Bebas / dll
            $table->text('kasus')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('no_putusan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Penyakit
        Schema::create('employee_diseases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('nama_penyakit');
            $table->date('tanggal')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        // Kontak Darurat
        Schema::create('employee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('hubungan')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Riwayat Peninjauan Masa Kerja (PMK)
        Schema::create('employee_pmk_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('no_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tmt')->nullable();
            $table->unsignedTinyInteger('tambah_tahun')->default(0);
            $table->unsignedTinyInteger('tambah_bulan')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('file_sk_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Kontrak PPPK
        Schema::create('employee_pppk_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_kontrak');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('masa_kerja')->nullable(); // mis. 1 Tahun / 2 Tahun
            $table->string('instansi')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_kontrak_path')->nullable();
            $table->timestamps();
        });

        // Sasaran Kinerja Pegawai (SKP)
        Schema::create('employee_skp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->year('tahun')->nullable();
            $table->string('periode')->nullable(); // Tahunan / Semester I / Semester II
            $table->text('uraian_kegiatan')->nullable();
            $table->text('target')->nullable();
            $table->text('realisasi')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('predikat')->nullable();
            $table->string('pejabat_penilai')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        // Angka Kredit
        Schema::create('employee_credit_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->year('tahun')->nullable();
            $table->string('unsur')->nullable(); // Utama / Penunjang
            $table->string('butir_kegiatan')->nullable();
            $table->decimal('nilai_angka_kredit', 8, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        // Indeks Profesionalitas ASN (IPASN)
        Schema::create('employee_ipasn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->year('tahun')->nullable();
            $table->string('komponen')->nullable(); // Integritas / Kualitas Kerja / Kompetensi / dll
            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('predikat')->nullable(); // Sangat Tinggi / Tinggi / Sedang / Rendah
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_ipasn');
        Schema::dropIfExists('employee_credit_scores');
        Schema::dropIfExists('employee_skp');
        Schema::dropIfExists('employee_pppk_contracts');
        Schema::dropIfExists('employee_pmk_histories');
        Schema::dropIfExists('employee_emergency_contacts');
        Schema::dropIfExists('employee_diseases');
        Schema::dropIfExists('employee_legal_statuses');
        Schema::dropIfExists('employee_inactive_periods');
        Schema::dropIfExists('employee_leaves');
        Schema::dropIfExists('employee_languages');
    }
};