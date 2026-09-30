<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('employees')->where('status_pegawai', 'Lainnya')->update(['status_pegawai' => 'Outsourcing']);

        $hasOutsourcing = DB::table('employee_types')->where('code', 'Outsourcing')->exists();
        if ($hasOutsourcing) {
            DB::table('employee_types')->where('code', 'Lainnya')->delete();
        } else {
            DB::table('employee_types')->where('code', 'Lainnya')->update(['code' => 'Outsourcing', 'name' => 'Outsourcing']);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->enum('status_pegawai', ['PNS', 'PPPK', 'Honorer', 'Kontrak', 'BLUD', 'Outsourcing'])->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('employees')->where('status_pegawai', 'Outsourcing')->update(['status_pegawai' => 'Lainnya']);

        $hasLainnya = DB::table('employee_types')->where('code', 'Lainnya')->exists();
        if ($hasLainnya) {
            DB::table('employee_types')->where('code', 'Outsourcing')->delete();
        } else {
            DB::table('employee_types')->where('code', 'Outsourcing')->update(['code' => 'Lainnya', 'name' => 'Lainnya']);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->enum('status_pegawai', ['PNS', 'PPPK', 'Honorer', 'Kontrak', 'BLUD', 'Lainnya'])->nullable()->change();
        });
    }
};