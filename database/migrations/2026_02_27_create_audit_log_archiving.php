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
        // This migration is informational only
        // To auto-delete old audit logs, add a scheduled task in app/Console/Kernel.php
        // Or manually run: AuditLog::where('created_at', '<', now()->subDays(90))->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
