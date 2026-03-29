<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('worker_off_day_exceptions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty. The old exception-based feature is deprecated.
    }
};
