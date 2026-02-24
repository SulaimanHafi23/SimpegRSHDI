<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * P5: Add missing indexes for frequently queried columns
     */
    public function up(): void
    {
        // P5-1: holidays — composite index for national holiday lookups
        Schema::table('holidays', function (Blueprint $table) {
            $table->index(['is_national', 'date'], 'holidays_national_date_index');
        });

        // P5-2: attendances — index on status for grouped queries
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['attendance_date', 'status'], 'attendances_date_status_index');
        });

        // P5-3: leave_requests — index for overlap & balance queries
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['worker_id', 'status', 'start_date', 'end_date'], 'leave_requests_worker_status_dates_index');
        });

        // P5-4: overtime_requests — index for status queries
        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->index(['worker_id', 'status'], 'overtime_requests_worker_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropIndex('holidays_national_date_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_date_status_index');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('leave_requests_worker_status_dates_index');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropIndex('overtime_requests_worker_status_index');
        });
    }
};
