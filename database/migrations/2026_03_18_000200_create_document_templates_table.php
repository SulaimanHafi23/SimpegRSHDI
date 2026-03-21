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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_type_id')->constrained('document_types')->onDelete('cascade');
            $table->string('employment_category')->nullable()->comment('ASN, PPPK, PPPK_PARUH_WAKTU, NON_ASN, OUTSOURCED, or NULL for all');
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('mime_type')->comment('PDF, DOCX, etc');
            $table->text('description')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indices
            $table->index('document_type_id');
            $table->index('employment_category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
