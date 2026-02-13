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
        Schema::table('shift_swap_requests', function (Blueprint $table) {
            $table->string('swap_type')->default('single_date')->after('target_shift_id');
            $table->date('swap_date')->nullable()->after('swap_type');
            $table->date('swap_start_date')->nullable()->after('swap_date');
            $table->date('swap_end_date')->nullable()->after('swap_start_date');
            $table->json('swap_dates')->nullable()->after('swap_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_swap_requests', function (Blueprint $table) {
            $table->dropColumn(['swap_type', 'swap_date', 'swap_start_date', 'swap_end_date', 'swap_dates']);
        });
    }
};
