<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Migrasi default bootstrap di-nonaktifkan karena tabel users,
     * password_reset_tokens, dan sessions sudah dibuat khusus untuk
     * login berbasis NIP oleh 2024_01_01_000002_create_users_table.php.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};