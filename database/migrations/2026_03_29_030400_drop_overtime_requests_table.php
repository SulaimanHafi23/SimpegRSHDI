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
        Schema::dropIfExists('overtime_requests');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->onDelete('cascade');
            $table->date('overtime_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('total_hours', 5, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->nullableForeignUuid('approved_by')->constrained('users')->onDelete('set null');
            $table->datetime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('approval_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['worker_id', 'overtime_date']);
            $table->index('status');
            $table->index('approved_by');
        });
    }
};
