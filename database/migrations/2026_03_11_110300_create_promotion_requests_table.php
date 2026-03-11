<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('worker_id');
            $table->string('promotion_type', 50)->default('regular');
            $table->string('current_rank', 50)->nullable();
            $table->string('current_rank_level', 20)->nullable();
            $table->string('proposed_rank', 50)->nullable();
            $table->string('proposed_rank_level', 20)->nullable();
            $table->decimal('current_base_salary', 15, 2)->default(0);
            $table->decimal('proposed_base_salary', 15, 2)->default(0);
            $table->date('effective_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_requests');
    }
};
