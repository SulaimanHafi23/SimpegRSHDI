<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_trips', function (Blueprint $table) {
            $table->string('trip_duration_type')->default('full_day')->after('end_date');
            $table->string('half_day_session')->nullable()->after('trip_duration_type');
            $table->string('transportation')->nullable()->after('half_day_session');
            $table->string('accommodation')->nullable()->after('transportation');
            $table->text('notes')->nullable()->after('accommodation');
        });
    }

    public function down(): void
    {
        Schema::table('business_trips', function (Blueprint $table) {
            $table->dropColumn([
                'trip_duration_type',
                'half_day_session',
                'transportation',
                'accommodation',
                'notes',
            ]);
        });
    }
};
