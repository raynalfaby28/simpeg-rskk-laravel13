<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jenis aset pegawai (master data). Contoh: Kendaraan, Elektronik, Perabot, dll.
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->nullable();
            $table->string('name')->unique();
            $table->string('category')->nullable(); // Kendaraan / Elektronik / Perabot / Lainnya
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Aset milik RS yang dipegang/dibawa pulang pegawai.
        Schema::create('employee_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_aset')->nullable(); // Deskripsi aset spesifik
            $table->string('merk')->nullable();
            $table->string('no_seri')->nullable();
            $table->string('no_inventaris')->nullable();
            $table->date('tanggal_terima')->nullable();
            $table->string('kondisi')->nullable(); // Baik / Cukup Baik / Rusak Ringan / Rusak Berat
            $table->string('status')->nullable(); // Dibawa Pulang / Disimpan di Rumah Sakit / Dikembalikan
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable(); // Kartu inventaris / BAST
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_assets');
        Schema::dropIfExists('asset_types');
    }
};