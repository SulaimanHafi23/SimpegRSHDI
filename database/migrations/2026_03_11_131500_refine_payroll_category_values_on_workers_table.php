<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE workers MODIFY payroll_category ENUM('government', 'internal', 'asn', 'pppk', 'non_asn', 'outsourced') NOT NULL DEFAULT 'non_asn'");
        }

        DB::table('workers')->where('payroll_category', 'government')->update(['payroll_category' => 'asn']);
        DB::table('workers')->where('payroll_category', 'internal')->update(['payroll_category' => 'non_asn']);

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE workers MODIFY payroll_category ENUM('asn', 'pppk', 'non_asn', 'outsourced') NOT NULL DEFAULT 'non_asn'");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE workers MODIFY payroll_category ENUM('government', 'internal', 'asn', 'pppk', 'non_asn', 'outsourced') NOT NULL DEFAULT 'internal'");
        }

        DB::table('workers')->where('payroll_category', 'asn')->update(['payroll_category' => 'government']);
        DB::table('workers')->where('payroll_category', 'non_asn')->update(['payroll_category' => 'internal']);

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE workers MODIFY payroll_category ENUM('government', 'internal', 'outsourced') NOT NULL DEFAULT 'internal'");
        }
    }
};
