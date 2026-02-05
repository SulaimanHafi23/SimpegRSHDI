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
        Schema::table('attendances', function (Blueprint $table) {
            // Track if check-in was done by admin
            $table->boolean('check_in_by_admin')->default(false)->after('distance_check_in');
            $table->foreignUuid('check_in_admin_id')->nullable()->after('check_in_by_admin')
                ->constrained('users')->nullOnDelete();
            
            // Track if check-out was done by admin
            $table->boolean('check_out_by_admin')->default(false)->after('distance_check_out');
            $table->foreignUuid('check_out_admin_id')->nullable()->after('check_out_by_admin')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['check_in_admin_id']);
            $table->dropForeign(['check_out_admin_id']);
            $table->dropColumn([
                'check_in_by_admin',
                'check_in_admin_id',
                'check_out_by_admin',
                'check_out_admin_id'
            ]);
        });
    }
};
