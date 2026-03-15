<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            if (!Schema::hasColumn('workers', 'payroll_category')) {
                $table->string('payroll_category', 30)->default('non_asn')->after('employment_status');
            }
            if (!Schema::hasColumn('workers', 'base_salary')) {
                $table->decimal('base_salary', 15, 2)->default(0)->after('payroll_category');
            }
            if (!Schema::hasColumn('workers', 'rank')) {
                $table->string('rank', 50)->nullable()->after('base_salary');
            }
            if (!Schema::hasColumn('workers', 'rank_level')) {
                $table->string('rank_level', 20)->nullable()->after('rank');
            }
            if (!Schema::hasColumn('workers', 'outsourced_vendor')) {
                $table->string('outsourced_vendor', 150)->nullable()->after('rank_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['payroll_category', 'base_salary', 'rank', 'rank_level', 'outsourced_vendor']);
        });
    }
};
