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
            Schema::hasColumn('attendance_photos', 'latitude') ? 'latitude' : null,
            Schema::hasColumn('attendance_photos', 'longitude') ? 'longitude' : null,
        ]));

        if (!empty($columnsToDrop)) {
            Schema::table('attendance_photos', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_photos', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_photos', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('taken_at');
            }
            if (!Schema::hasColumn('attendance_photos', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }
};
