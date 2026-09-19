<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'app_name' => 'SIMPEG RSKK',
            'app_tagline' => 'Sistem Informasi Kepegawaian',
            'rs_name' => 'RSUD Kesehatan Kerja',
            'rs_address' => '',
            'rs_phone' => '',
            'rs_email' => '',
        ];
        foreach ($defaults as $key => $value) {
            DB::table('settings')->insertOrIgnore(['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};