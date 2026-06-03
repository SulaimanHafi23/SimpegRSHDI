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
        // Update leave_requests table to add manager_verified status
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'manager_verified', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");

        // Add manager_id and manager_verified_at columns to leave_requests if not exists
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'manager_id')) {
                $table->foreignUuid('manager_id')->nullable()->after('worker_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_requests', 'manager_verified_at')) {
                $table->timestamp('manager_verified_at')->nullable()->after('manager_id');
            }
        });

        // Update business_trips table to add manager_verified status
        DB::statement("ALTER TABLE business_trips MODIFY COLUMN status ENUM('pending', 'manager_verified', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");

        // Add manager_id and manager_verified_at columns to business_trips if not exists
        Schema::table('business_trips', function (Blueprint $table) {
            if (!Schema::hasColumn('business_trips', 'manager_id')) {
                $table->foreignUuid('manager_id')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('business_trips', 'manager_verified_at')) {
                $table->timestamp('manager_verified_at')->nullable()->after('manager_id');
            }
        });

        // Update shift_swap_requests status enum to better define the flow
        DB::statement("ALTER TABLE shift_swap_requests MODIFY COLUMN status ENUM('pending', 'manager_verified', 'approved', 'rejected', 'executed', 'cancelled') DEFAULT 'pending'");

        // Add manager_verified_at columns to shift_swap_requests if not exists
        Schema::table('shift_swap_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('shift_swap_requests', 'manager_verified_at')) {
                $table->timestamp('manager_verified_at')->nullable()->after('manager_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert leave_requests status enum
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");

        // Remove new columns from leave_requests
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'manager_id')) {
                $table->dropForeign(['leave_requests_manager_id_foreign']);
                $table->dropColumn('manager_id');
            }
            if (Schema::hasColumn('leave_requests', 'manager_verified_at')) {
                $table->dropColumn('manager_verified_at');
            }
        });

        // Revert business_trips status enum
        DB::statement("ALTER TABLE business_trips MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");

        // Remove new columns from business_trips
        Schema::table('business_trips', function (Blueprint $table) {
            if (Schema::hasColumn('business_trips', 'manager_id')) {
                $table->dropForeign(['business_trips_manager_id_foreign']);
                $table->dropColumn('manager_id');
            }
            if (Schema::hasColumn('business_trips', 'manager_verified_at')) {
                $table->dropColumn('manager_verified_at');
            }
        });

        // Revert shift_swap_requests status enum
        DB::statement("ALTER TABLE shift_swap_requests MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'awaiting_approval', 'approved', 'executed') DEFAULT 'pending'");

        // Remove new columns from shift_swap_requests
        Schema::table('shift_swap_requests', function (Blueprint $table) {
            if (Schema::hasColumn('shift_swap_requests', 'manager_verified_at')) {
                $table->dropColumn('manager_verified_at');
            }
        });
    }
};
