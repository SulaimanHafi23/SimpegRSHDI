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
        Schema::create('worker_off_days', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('worker_id');
            $table->json('day_of_week'); // [0, 3, 5] = Sunday, Wednesday, Friday
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('reason')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->foreign('worker_id')->references('id')->on('workers')->cascadeOnDelete();
            $table->index(['worker_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_off_days');
    }
};
