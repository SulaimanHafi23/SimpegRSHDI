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
            if (!Schema::hasColumn('shift_swap_requests', 'manager_verified_at')) {
                $table->timestamp('manager_verified_at')->nullable()->after('manager_id');
            }
            if (!Schema::hasColumn('shift_swap_requests', 'approved_by')) {
                $table->foreignUuid('approved_by')->nullable()->after('manager_verified_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('shift_swap_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_swap_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['manager_verified_at', 'approved_by', 'approved_at']);
        });
    }
};
