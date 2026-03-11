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
        Schema::create('business_trips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('destination');
            $table->text('purpose')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('itinerary')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['worker_id', 'status']);
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['worker_id', 'start_date', 'end_date', 'status'], 'idx_business_trips_worker_dates_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trips');
    }
};