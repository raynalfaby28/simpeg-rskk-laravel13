<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat Pendidikan
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_level_id')->constrained('education_levels');
            $table->string('institution'); // nama sekolah/universitas
            $table->string('faculty')->nullable();
            $table->string('major')->nullable(); // program studi
            $table->string('no_ijazah')->nullable();
            $table->year('tahun_masuk')->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->string('status')->nullable(); // lulus / sedang berjalan
            $table->string('file_ijazah_path')->nullable();
            $table->string('file_transkrip_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Jabatan
        Schema::create('employee_position_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions');
            $table->foreignId('work_unit_id')->nullable()->constrained('work_units')->nullOnDelete();
            $table->string('no_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tmt')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('alasan_perubahan')->nullable();
            $table->string('file_sk_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Pangkat & Golongan
        Schema::create('employee_rank_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rank_id')->constrained('ranks');
            $table->string('no_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tmt')->nullable();
            $table->unsignedTinyInteger('masa_kerja_tahun')->default(0);
            $table->unsignedTinyInteger('masa_kerja_bulan')->default(0);
            $table->string('file_sk_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Kenaikan Gaji (akses dibatasi via Policy)
        Schema::create('employee_salary_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('gaji_pokok', 12, 2);
            $table->string('no_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tmt')->nullable();
            $table->unsignedTinyInteger('masa_kerja_tahun')->default(0);
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->string('file_sk_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Mutasi
        Schema::create('employee_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_mutasi');
            $table->foreignId('unit_asal_id')->nullable()->constrained('work_units')->nullOnDelete();
            $table->foreignId('unit_tujuan_id')->nullable()->constrained('work_units')->nullOnDelete();
            $table->string('jabatan_lama')->nullable();
            $table->string('jabatan_baru')->nullable();
            $table->date('tanggal_mutasi')->nullable();
            $table->string('no_sk')->nullable();
            $table->text('alasan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_sk_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Pelatihan & Sertifikasi
        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('nama_pelatihan');
            $table->string('penyelenggara')->nullable();
            $table->string('jenis_pelatihan')->nullable(); // pelatihan / sertifikasi / diklat
            $table->string('tempat')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('no_sertifikat')->nullable();
            $table->date('masa_berlaku')->nullable();
            $table->string('file_sertifikat_path')->nullable();
            $table->timestamps();
        });

        // Riwayat Penghargaan
        Schema::create('employee_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('nama_penghargaan');
            $table->string('jenis_penghargaan')->nullable();
            $table->string('pemberi_penghargaan')->nullable();
            $table->string('tingkat')->nullable();
            $table->year('tahun')->nullable();
            $table->string('no_penghargaan')->nullable();
            $table->string('file_path')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Riwayat Hukum/Disiplin (akses sangat terbatas)
        Schema::create('employee_disciplines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_pelanggaran');
            $table->date('tanggal')->nullable();
            $table->string('tingkat_pelanggaran')->nullable();
            $table->string('sanksi')->nullable();
            $table->string('no_keputusan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        // Data Keluarga (pasangan & anak dalam satu tabel dengan kolom type)
        Schema::create('employee_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['pasangan', 'anak', 'orang_tua']);
            $table->string('nama');
            $table->string('nik')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('status')->nullable(); // hidup/meninggal, kawin/belum, dst
            $table->boolean('status_tanggungan')->default(false);
            $table->timestamps();
        });

        // Pusat Dokumen (polymorphic-ready: employee + jenis dokumen)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_dokumen'); // KTP, KK, NPWP, BPJS, Ijazah, SK Pengangkatan, dll
            $table->string('no_dokumen')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('file_path');
            $table->enum('status_verifikasi', ['belum_diverifikasi', 'terverifikasi', 'ditolak'])->default('belum_diverifikasi');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('employee_families');
        Schema::dropIfExists('employee_disciplines');
        Schema::dropIfExists('employee_awards');
        Schema::dropIfExists('employee_trainings');
        Schema::dropIfExists('employee_mutations');
        Schema::dropIfExists('employee_salary_histories');
        Schema::dropIfExists('employee_rank_histories');
        Schema::dropIfExists('employee_position_histories');
        Schema::dropIfExists('employee_educations');
    }
};
