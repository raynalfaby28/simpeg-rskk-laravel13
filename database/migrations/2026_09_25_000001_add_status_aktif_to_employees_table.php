<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('status_aktif')->default(true)->after('is_draft');
            $table->string('alasan_nonaktif', 100)->nullable()->after('status_aktif');
            $table->date('tanggal_nonaktif')->nullable()->after('alasan_nonaktif');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['status_aktif', 'alasan_nonaktif', 'tanggal_nonaktif']);
        });
    }
};