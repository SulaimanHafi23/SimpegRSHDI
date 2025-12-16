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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignUuid('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->date('attendance_date');
            
            // Check In
            $table->dateTime('check_in');
            $table->decimal('check_in_latitude', 10, 8);
            $table->decimal('check_in_longitude', 11, 8);
            $table->integer('distance_check_in')->comment('dalam meter');
            
            // Check Out (nullable)
            $table->dateTime('check_out')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->integer('distance_check_out')->nullable()->comment('dalam meter');
            
            // Status & Analytics
            $table->enum('status', ['present', 'absent', 'leave', 'sick', 'permission'])->default('present');
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->boolean('is_early_leave')->default(false);
            $table->integer('early_leave_minutes')->default(0);
            $table->boolean('is_outside_radius')->default(false);
            $table->integer('overtime_minutes')->nullable()->comment('lembur dalam menit');
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->unique(['worker_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
            $table->index(['worker_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
