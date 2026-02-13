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
        Schema::table('departments', function (Blueprint $table) {
            $table->boolean('requires_holiday_attendance')->default(false)->after('is_active')
                  ->comment('Jika true, pegawai di departemen ini tetap wajib absen pada hari libur nasional (contoh: IGD, Rawat Inap)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('requires_holiday_attendance');
        });
    }
};
