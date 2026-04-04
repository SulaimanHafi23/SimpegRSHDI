<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_trips') && !Schema::hasColumn('business_trips', 'supporting_document_path')) {
            Schema::table('business_trips', function (Blueprint $table) {
                $table->string('supporting_document_path')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('business_trips') && Schema::hasColumn('business_trips', 'supporting_document_path')) {
            Schema::table('business_trips', function (Blueprint $table) {
                $table->dropColumn('supporting_document_path');
            });
        }
    }
};
