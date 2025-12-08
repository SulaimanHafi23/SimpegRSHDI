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
        Schema::create('absents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignUuid('worker_shift_schedule_id')->nullable()->constrained('worker_shift_schedules')->nullOnDelete();
            $table->foreignUuid('business_trip_id')->nullable()->constrained('business_trips')->nullOnDelete();
            
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            
            $table->decimal('distance_from_office', 10, 2)->nullable()->comment('in meters');
            
            $table->text('reason')->nullable();
            $table->enum('status', [
                'Present', 
                'Absent', 
                'Permission', 
                'Sick', 
                'Leave', 
                'Business_Trip'
            ])->default('Present');
            $table->enum('absent_type', ['Normal', 'Business_Trip', 'Remote'])->default('Normal');
            
            $table->boolean('present_by_admin')->default(false);
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->nullable();
            $table->boolean('is_outside_radius')->default(false);
            
            $table->text('notes')->nullable();
            $table->string('photo_check_in')->nullable();
            $table->string('photo_check_out')->nullable();
            
            $table->timestamps();

            $table->index(['worker_id', 'check_in']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absents');
    }
};
