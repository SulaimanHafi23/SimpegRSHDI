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
        Schema::create('worker_off_day_exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('worker_id');
            $table->date('off_date');
            $table->enum('type', ['single', 'recurring'])->default('single');
            $table->json('recurring_pattern')->nullable(); // {day_of_week: [2, 5], until: '2026-12-31'}
            $table->string('reason')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->foreign('worker_id')->references('id')->on('workers')->cascadeOnDelete();
            $table->index('worker_id');
            $table->index('off_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_off_day_exceptions');
    }
};
