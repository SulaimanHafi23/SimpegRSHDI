<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('genders');
        Schema::dropIfExists('religions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('genders')) {
            Schema::create('genders', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('religions')) {
            Schema::create('religions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }
};
