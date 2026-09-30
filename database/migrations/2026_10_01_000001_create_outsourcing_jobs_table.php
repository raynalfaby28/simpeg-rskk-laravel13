<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outsourcing_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('outsourcing_job_id')
                ->nullable()
                ->after('blud_category_id')
                ->constrained('outsourcing_jobs')
                ->nullOnDelete();
        });

        // Jenis pekerjaan outsourcing (Security, Cleaning Service, Driver, Lainnya)
        // yang sempat tersimpan di tabel "Jenis BLUD" dipindahkan ke sini.
        if (Schema::hasTable('blud_categories')) {
            $moved = [];
            foreach (DB::table('blud_categories')->get(['id', 'name']) as $row) {
                DB::table('outsourcing_jobs')->insertOrIgnore(['name' => $row->name]);
                $moved[] = $row->name;
            }

            if ($moved) {
                $usedIds = DB::table('employees')
                    ->whereNotNull('blud_category_id')
                    ->pluck('blud_category_id')
                    ->unique()
                    ->map(fn ($v) => (int) $v);

                if (Schema::hasColumn('work_units', 'blud_category_id')) {
                    $usedIds = $usedIds->merge(
                        DB::table('work_units')
                            ->whereNotNull('blud_category_id')
                            ->pluck('blud_category_id')
                    )->unique()->map(fn ($v) => (int) $v);
                }

                DB::table('blud_categories')
                    ->whereIn('name', $moved)
                    ->whereNotIn('id', $usedIds)
                    ->delete();
            }
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outsourcing_job_id');
        });

        Schema::dropIfExists('outsourcing_jobs');
    }
};