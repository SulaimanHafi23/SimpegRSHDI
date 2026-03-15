<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('worker_id');
            $table->uuid('promotion_request_id')->nullable();
            $table->string('promotion_type', 50)->default('regular');
            $table->string('old_rank', 50)->nullable();
            $table->string('old_rank_level', 20)->nullable();
            $table->string('new_rank', 50)->nullable();
            $table->string('new_rank_level', 20)->nullable();
            $table->decimal('old_base_salary', 15, 2)->default(0);
            $table->decimal('new_base_salary', 15, 2)->default(0);
            $table->date('effective_date');
            $table->uuid('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->foreign('promotion_request_id')->references('id')->on('promotion_requests')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_histories');
    }
};
