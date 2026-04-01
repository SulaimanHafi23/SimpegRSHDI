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
        $columnsToDrop = array_values(array_filter([
            Schema::hasColumn('attendances', 'check_in_latitude') ? 'check_in_latitude' : null,
            Schema::hasColumn('attendances', 'check_in_longitude') ? 'check_in_longitude' : null,
            Schema::hasColumn('attendances', 'check_out_latitude') ? 'check_out_latitude' : null,
            Schema::hasColumn('attendances', 'check_out_longitude') ? 'check_out_longitude' : null,
        ]));

        if (!empty($columnsToDrop)) {
            Schema::table('attendances', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 8)->nullable()->after('check_in');
            }
            if (!Schema::hasColumn('attendances', 'check_in_longitude')) {
                $table->decimal('check_in_longitude', 11, 8)->nullable()->after('check_in_latitude');
            }
            if (!Schema::hasColumn('attendances', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 8)->nullable()->after('check_out');
            }
            if (!Schema::hasColumn('attendances', 'check_out_longitude')) {
                $table->decimal('check_out_longitude', 11, 8)->nullable()->after('check_out_latitude');
            }
        });
    }
};
