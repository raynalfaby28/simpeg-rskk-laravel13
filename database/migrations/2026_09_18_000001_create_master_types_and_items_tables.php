<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('label', 100);
            $table->string('description', 255)->nullable();
            $table->unsignedSmallInteger('sort')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('master_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_type_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64)->nullable();
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->unsignedSmallInteger('urutan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('master_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_items');
        Schema::dropIfExists('master_types');
    }
};