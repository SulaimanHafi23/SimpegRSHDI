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
        if (Schema::hasColumn('attendances', 'overtime_minutes')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('overtime_minutes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('attendances', 'overtime_minutes')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->integer('overtime_minutes')->nullable()->comment('lembur dalam menit')->after('early_leave_minutes');
            });
        }
    }
};
