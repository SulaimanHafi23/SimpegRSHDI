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
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'location_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                try {
                    $table->dropForeign(['location_id']);
                } catch (Throwable $e) {
                    // Ignore when the foreign key is already missing.
                }

                $table->dropColumn('location_id');
            });
        }

        Schema::dropIfExists('locations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->text('address');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->integer('radius')->default(100);
                $table->boolean('enforce_geofence')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances', 'location_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->foreignUuid('location_id')->nullable()->after('shift_id')->constrained('locations')->nullOnDelete();
            });
        }
    }
};
