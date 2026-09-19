<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_assets', function (Blueprint $table) {
            $table->renameColumn('tanggal_terima', 'tanggal_mulai');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            $table->string('surat_mutasi_path')->nullable()->after('keterangan');
            $table->string('surat_pengembalian_path')->nullable()->after('surat_mutasi_path');
        });
    }

    public function down(): void
    {
        Schema::table('employee_assets', function (Blueprint $table) {
            $table->dropColumn(['surat_pengembalian_path', 'surat_mutasi_path', 'tanggal_selesai']);
            $table->renameColumn('tanggal_mulai', 'tanggal_terima');
        });
    }
};