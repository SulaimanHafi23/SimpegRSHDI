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
        Schema::table('worker_shifts', function (Blueprint $table) {
            $table->json('custom_working_days')
                ->nullable()
                ->after('rotating_days')
                ->comment('Array of ISO day numbers 1(Mon) - 7(Sun) for custom pattern');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('worker_shifts', 'custom_working_days')) {
                $table->dropColumn('custom_working_days');
            }
        });
    }
};
