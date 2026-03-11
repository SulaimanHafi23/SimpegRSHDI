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
        Schema::create('shift_swap_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('requester_id');
            $table->uuid('target_worker_id')->nullable();

            $table->uuid('requester_shift_id');
            $table->uuid('target_shift_id')->nullable();
            $table->string('swap_type')->default('single_date');
            $table->date('swap_date')->nullable();
            $table->date('swap_start_date')->nullable();
            $table->date('swap_end_date')->nullable();
            $table->json('swap_dates')->nullable();

            $table->enum('status', ['pending','accepted','rejected','cancelled','awaiting_approval','approved','executed'])->default('pending');

            $table->boolean('requires_manager_approval')->default(false);
            $table->uuid('manager_id')->nullable();
            $table->timestamp('manager_approved_at')->nullable();

            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->uuid('executed_by')->nullable();
            $table->timestamp('executed_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('requester_id')->references('id')->on('workers')->cascadeOnDelete();
            $table->foreign('target_worker_id')->references('id')->on('workers')->nullOnDelete();
            $table->foreign('requester_shift_id')->references('id')->on('worker_shifts')->cascadeOnDelete();
            $table->foreign('target_shift_id')->references('id')->on('worker_shifts')->nullOnDelete();
            $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('executed_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['requester_id']);
            $table->index(['target_worker_id']);
            $table->index(['status']);
        });

        Schema::table('shift_overrides', function (Blueprint $table) {
            $table->foreign('shift_swap_request_id')
                ->references('id')
                ->on('shift_swap_requests')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_overrides', function (Blueprint $table) {
            $table->dropForeign(['shift_swap_request_id']);
        });

        Schema::dropIfExists('shift_swap_requests');
    }
};
