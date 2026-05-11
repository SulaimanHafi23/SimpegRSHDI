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
        Schema::table('shift_swap_requests', function (Blueprint $table) {
            // Using DB statement because Schema builder doesn't support changing enum values easily in all DB versions
            DB::statement("ALTER TABLE shift_swap_requests MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'cancelled', 'awaiting_approval', 'manager_verified', 'approved', 'executed') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_swap_requests', function (Blueprint $table) {
            DB::statement("ALTER TABLE shift_swap_requests MODIFY COLUMN status ENUM('pending', 'manager_verified', 'approved', 'rejected', 'executed', 'cancelled') DEFAULT 'pending'");
        });
    }
};
