<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('shift_swap_requests') && Schema::hasColumn('shift_swap_requests', 'swap_date')) {
            // Backfill normalized range fields for legacy single_date rows.
            DB::statement("\n                UPDATE shift_swap_requests\n                SET\n                    swap_start_date = COALESCE(swap_start_date, swap_date),\n                    swap_end_date = COALESCE(swap_end_date, swap_date)\n                WHERE swap_date IS NOT NULL\n            ");
        }

        Schema::table('shift_swap_requests', function (Blueprint $table) {
            if (Schema::hasColumn('shift_swap_requests', 'swap_date')) {
                $table->dropColumn('swap_date');
            }

            if (Schema::hasColumn('shift_swap_requests', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_swap_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('shift_swap_requests', 'swap_date')) {
                $table->date('swap_date')->nullable()->after('swap_type');
            }

            if (!Schema::hasColumn('shift_swap_requests', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('requested_at');
            }
        });

        DB::statement("\n            UPDATE shift_swap_requests\n            SET swap_date = swap_start_date\n            WHERE swap_type = 'single_date'\n              AND swap_start_date IS NOT NULL\n              AND swap_end_date IS NOT NULL\n              AND swap_start_date = swap_end_date\n        ");
    }
};
