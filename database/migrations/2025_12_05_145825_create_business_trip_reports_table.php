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
        Schema::create('business_trip_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_trip_id')->constrained()->cascadeOnDelete();
            $table->string('report_title');
            $table->text('report_content');
            $table->string('attachment_url')->nullable();
            $table->timestamp('submitted_at');
            $table->enum('status', ['Draft', 'Submitted', 'Approved', 'Revision'])->default('Draft');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trip_reports');
    }
};
