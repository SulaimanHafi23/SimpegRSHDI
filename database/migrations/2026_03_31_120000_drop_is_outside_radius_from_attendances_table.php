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
        if (Schema::hasColumn('attendances', 'is_outside_radius')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('is_outside_radius');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('attendances', 'is_outside_radius')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->boolean('is_outside_radius')->default(false)->after('early_leave_minutes');
            });
        }
    }
};
