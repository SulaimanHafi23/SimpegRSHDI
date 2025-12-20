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
        Schema::create('department_document_type', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignUuid('document_type_id')->constrained('document_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['department_id', 'document_type_id'], 'dept_doc_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_document_type');
    }
};
