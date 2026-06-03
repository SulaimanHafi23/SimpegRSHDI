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
        Schema::table('business_trips', function (Blueprint $table) {
            if (!Schema::hasColumn('business_trips', 'manager_id')) {
                $table->foreignUuid('manager_id')->nullable()->after('estimated_cost')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('business_trips', 'manager_verified_at')) {
                $table->timestamp('manager_verified_at')->nullable()->after('manager_id');
            }
            
            // Update status enum by modifying the column
            // In Laravel/MySQL, changing enum can be tricky, but we can use raw query
            DB::statement("ALTER TABLE business_trips MODIFY COLUMN status ENUM('pending', 'manager_verified', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_trips', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['manager_id', 'manager_verified_at']);
            DB::statement("ALTER TABLE business_trips MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
        });
    }
};
