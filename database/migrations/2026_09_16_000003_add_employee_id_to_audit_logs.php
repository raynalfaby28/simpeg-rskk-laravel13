<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('audit_logs', 'employee_id')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->foreignId('employee_id')->nullable()->after('user_id')->constrained('employees')->nullOnDelete();
                $table->index(['employee_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropIndex(['employee_id', 'created_at']);
            $table->dropColumn('employee_id');
        });
    }
};