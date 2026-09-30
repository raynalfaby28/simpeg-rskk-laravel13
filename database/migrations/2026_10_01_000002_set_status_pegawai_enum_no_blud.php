<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Status Pegawai tanpa opsi BLUD/Lainnya: PNS/PPPK/Honorer/Kontrak
        // termasuk ruang lingkup BLUD; Outsourcing dikelola terpisah.
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE employees MODIFY COLUMN status_pegawai ENUM('PNS','PPPK','Honorer','Kontrak','Outsourcing') NULL");
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE employees MODIFY COLUMN status_pegawai ENUM('PNS','PPPK','Honorer','Kontrak','BLUD','Lainnya') NULL");
        }
    }
};