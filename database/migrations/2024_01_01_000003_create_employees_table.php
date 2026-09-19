<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Identitas dasar
            $table->string('nip')->unique();
            $table->string('nip_lama')->nullable();
            $table->string('nik')->nullable()->unique();
            $table->string('no_kk')->nullable();
            $table->string('nama_lengkap');
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->string('nama_panggilan')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Lainnya'])->nullable();
            $table->enum('status_perkawinan', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->nullable();
            $table->string('golongan_darah', 5)->nullable();
            $table->string('foto_path')->nullable();

            // Identitas administratif
            $table->string('no_npwp')->nullable();
            $table->string('no_bpjs')->nullable();
            $table->string('no_karpeg')->nullable();
            $table->string('no_karis_karsu')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('bank')->nullable();
            $table->string('no_taspen')->nullable();
            $table->enum('bapertarum', ['Sudah Diambil', 'Belum Diambil', 'Tidak Ada'])->nullable();

            // Status kepegawaian
            $table->foreignId('employee_category_id')->nullable()->constrained('employee_categories')->nullOnDelete();
            $table->foreignId('employment_status_id')->nullable()->constrained('employment_statuses')->nullOnDelete();
            $table->enum('status_pegawai', ['PNS', 'PPPK', 'Honorer', 'Kontrak', 'Lainnya'])->nullable();
            $table->enum('jenis_asn', ['PNS', 'PPPK'])->nullable();
            $table->string('status_calon')->nullable(); // CPNS / Calon PPPK / -
            $table->string('jenis_pns')->nullable();
            $table->string('kedudukan_pegawai')->nullable();
            $table->string('mekanisme_mutasi')->nullable();
            $table->boolean('kepemilikan_kpe')->default(false);

            // Pendidikan ringkas (detail lengkap ada di riwayat)
            $table->foreignId('pendidikan_awal_id')->nullable()->constrained('education_levels')->nullOnDelete();
            $table->year('tahun_pendidikan_awal')->nullable();
            $table->foreignId('pendidikan_akhir_id')->nullable()->constrained('education_levels')->nullOnDelete();
            $table->year('tahun_pendidikan_akhir')->nullable();
            $table->boolean('izin_pemakaian_gelar')->default(false);

            // Jabatan & organisasi (posisi terkini, history di tabel terpisah)
            $table->enum('jenis_jabatan', ['struktural', 'fungsional', 'pelaksana'])->nullable();
            $table->string('eselon')->nullable();
            $table->date('tmt_eselon')->nullable();
            $table->foreignId('current_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->date('tmt_jabatan')->nullable();
            $table->string('tugas_tambahan_1')->nullable();
            $table->date('tmt_tugas_tambahan_1')->nullable();
            $table->string('tugas_tambahan_2')->nullable();
            $table->date('tmt_tugas_tambahan_2')->nullable();
            $table->foreignId('work_unit_id')->nullable()->constrained('work_units')->nullOnDelete();
            $table->date('tmt_skpd')->nullable();
            $table->string('instansi_dipekerjakan')->nullable();

            // Gaji & golongan
            $table->foreignId('golongan_awal_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->date('tmt_golongan_awal')->nullable();
            $table->foreignId('golongan_akhir_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->date('tmt_golongan_akhir')->nullable();
            $table->unsignedTinyInteger('masa_kerja_tahun')->default(0);
            $table->unsignedTinyInteger('masa_kerja_bulan')->default(0);
            $table->decimal('gaji_pokok', 12, 2)->nullable();
            $table->date('tmt_gaji_berkala_terbaru')->nullable();

            // Alamat rumah
            $table->text('alamat_rumah')->nullable();
            $table->string('rt_rumah', 5)->nullable();
            $table->string('rw_rumah', 5)->nullable();
            $table->string('kelurahan_rumah')->nullable();
            $table->string('kecamatan_rumah')->nullable();
            $table->string('kabkota_rumah')->nullable();
            $table->string('provinsi_rumah')->nullable();
            $table->string('kodepos_rumah', 10)->nullable();

            // Alamat domisili KTP
            $table->text('alamat_domisili_ktp')->nullable();
            $table->string('rt_domisili', 5)->nullable();
            $table->string('rw_domisili', 5)->nullable();
            $table->string('kelurahan_domisili')->nullable();
            $table->string('kecamatan_domisili')->nullable();
            $table->string('kabkota_domisili')->nullable();
            $table->string('provinsi_domisili')->nullable();
            $table->string('kodepos_domisili', 10)->nullable();

            // Kontak
            $table->string('telp')->nullable();
            $table->string('hp')->nullable();
            $table->string('email_pribadi')->nullable();
            $table->string('email_resmi')->nullable();

            $table->timestamp('data_updated_at')->nullable(); // "Update Terakhir"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
