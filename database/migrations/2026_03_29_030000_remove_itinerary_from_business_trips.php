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
        if (Schema::hasTable('business_trips') && Schema::hasColumn('business_trips', 'itinerary')) {
            Schema::table('business_trips', function (Blueprint $table) {
                $table->dropColumn('itinerary');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('business_trips') && !Schema::hasColumn('business_trips', 'itinerary')) {
            Schema::table('business_trips', function (Blueprint $table) {
                $table->json('itinerary')->nullable()->after('rejection_reason');
            });
        }
    }
};
