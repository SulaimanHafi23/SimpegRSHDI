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
        Schema::table('shift_overrides', function (Blueprint $table) {
            $table->foreignUuid('shift_swap_request_id')
                  ->nullable()
                  ->after('created_by')
                  ->constrained('shift_swap_requests')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_overrides', function (Blueprint $table) {
            $table->dropForeign(['shift_swap_request_id']);
            $table->dropColumn('shift_swap_request_id');
        });
    }
};
