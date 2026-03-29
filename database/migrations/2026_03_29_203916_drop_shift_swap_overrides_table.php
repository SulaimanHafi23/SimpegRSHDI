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
        Schema::dropIfExists('shift_swap_overrides');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreating the table wouldn't typically restore the data,
        // but here is the schema just in case.
        Schema::create('shift_swap_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_swap_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('override_date');
            $table->timestamps();
        });
    }
};
