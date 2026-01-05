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
        if (Schema::hasTable('worker_documents')) {
            Schema::table('worker_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('worker_documents', 'department_document_type_id')) {
                    $table->foreignUuid('department_document_type_id')->nullable()->constrained('department_document_type')->nullOnDelete()->after('document_type_id');
                    $table->index(['worker_id', 'department_document_type_id']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_documents', function (Blueprint $table) {
            if (Schema::hasColumn('worker_documents', 'department_document_type_id')) {
                $table->dropForeign(['department_document_type_id']);
                $table->dropIndex(['worker_id', 'department_document_type_id']);
                $table->dropColumn('department_document_type_id');
            }
        });
    }
};
