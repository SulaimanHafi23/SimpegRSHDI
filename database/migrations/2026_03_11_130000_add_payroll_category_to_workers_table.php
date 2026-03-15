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
                $table->enum('payroll_category', ['asn', 'pppk', 'non_asn', 'outsourced'])
                    ->default('non_asn')
                    ->after('current_grade');
            }

            if (!Schema::hasColumn('workers', 'outsourced_vendor')) {
                $table->string('outsourced_vendor', 150)->nullable()->after('payroll_category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            if (Schema::hasColumn('workers', 'outsourced_vendor')) {
                $table->dropColumn('outsourced_vendor');
            }

            if (Schema::hasColumn('workers', 'payroll_category')) {
                $table->dropColumn('payroll_category');
            }
        });
    }
};
