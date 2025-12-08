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
        Schema::create('worker_shift_schedules', function (Blueprint $table) {
            // PRIMARY KEY
            $table->uuid('id')->primary();
            
            // FOREIGN KEYS
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignUuid('shift_id')->constrained('shifts')->cascadeOnDelete();
            
            // DEFAULT RECURRING SCHEDULE (based on day of week)
            $table->enum('day_of_week', [
                'monday', 'tuesday', 'wednesday', 'thursday', 
                'friday', 'saturday', 'sunday'
            ])->nullable();
            $table->boolean('is_default')->default(false);
            
            // OVERRIDE/EXCEPTION SCHEDULE (specific date)
            $table->date('schedule_date')->nullable();
            $table->boolean('is_override')->default(false);
            $table->foreignUuid('replaced_worker_id')->nullable()->constrained('workers')->nullOnDelete();
            
            // COMMON FIELDS
            $table->enum('status', ['Active', 'Inactive', 'Completed', 'Cancelled'])->default('Active');
            $table->text('notes')->nullable();
            
            // TIMESTAMPS
            $table->timestamps();
            
            // INDEXES for Performance
            $table->index(['worker_id', 'day_of_week'], 'wss_worker_day_idx');
            $table->index(['worker_id', 'schedule_date'], 'wss_worker_date_idx');
            $table->index(['schedule_date'], 'wss_date_idx');
            $table->index(['day_of_week', 'is_default'], 'wss_day_default_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_shift_schedules');
    }
};
