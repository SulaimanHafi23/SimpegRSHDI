<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_document_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('employment_category', 30);
            $table->string('process_type', 50);
            $table->foreignUuid('document_type_id')->constrained('document_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('expiration_buffer_days')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employment_category', 'process_type', 'is_active'], 'idx_emp_doc_req_category_process_active');
            $table->unique(['employment_category', 'process_type', 'document_type_id'], 'emp_doc_req_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_document_requirements');
    }
};
