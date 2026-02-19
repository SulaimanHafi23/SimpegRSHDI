<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah composite indexes untuk optimasi query performa.
     *
     * Index ini menargetkan query yang paling sering dijalankan:
     * - Attendance filtering by worker + date + status
     * - LeaveRequest lookup by worker + status + date range
     * - OvertimeRequest lookup by worker + date + status
     * - Worker filtering by department + status
     * - WorkerShift lookup by worker + active status
     */
    public function up(): void
    {
        // workers: department_id + status (sering difilter bersamaan)
        Schema::table('workers', function (Blueprint $table) {
            $table->index(['department_id', 'status'], 'idx_workers_dept_status');
        });

        // attendances: worker_id + status + is_late (untuk statistik)
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['worker_id', 'status', 'is_late'], 'idx_attendances_worker_status_late');
            $table->index(['attendance_date', 'worker_id'], 'idx_attendances_date_worker');
        });

        // leave_requests: worker_id + status + start_date + end_date (cek cuti aktif)
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['worker_id', 'status', 'start_date', 'end_date'], 'idx_leave_req_worker_status_dates');
        });

        // overtime_requests: worker_id + overtime_date + status (untuk summary)
        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->index(['worker_id', 'overtime_date', 'status'], 'idx_overtime_req_worker_date_status');
        });

        // worker_shifts: worker_id + is_active (sering dicari shift aktif)
        Schema::table('worker_shifts', function (Blueprint $table) {
            $table->index(['worker_id', 'is_active'], 'idx_worker_shifts_worker_active');
        });

        // business_trips: worker_id + start_date + end_date + status
        Schema::table('business_trips', function (Blueprint $table) {
            $table->index(['worker_id', 'start_date', 'end_date', 'status'], 'idx_business_trips_worker_dates_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropIndex('idx_workers_dept_status');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_worker_status_late');
            $table->dropIndex('idx_attendances_date_worker');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_req_worker_status_dates');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropIndex('idx_overtime_req_worker_date_status');
        });

        Schema::table('worker_shifts', function (Blueprint $table) {
            $table->dropIndex('idx_worker_shifts_worker_active');
        });

        Schema::table('business_trips', function (Blueprint $table) {
            $table->dropIndex('idx_business_trips_worker_dates_status');
        });
    }
};
