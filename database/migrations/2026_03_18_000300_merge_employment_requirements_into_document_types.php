<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_types')) {
            return;
        }

        Schema::table('document_types', function (Blueprint $table) {
            if (!Schema::hasColumn('document_types', 'employment_category')) {
                $table->string('employment_category', 30)->default('all')->after('is_universal');
            }

            if (!Schema::hasColumn('document_types', 'process_type')) {
                $table->string('process_type', 50)->default('onboarding')->after('employment_category');
            }

            if (!Schema::hasColumn('document_types', 'expiration_buffer_days')) {
                $table->unsignedSmallInteger('expiration_buffer_days')->default(0)->after('process_type');
            }

            if (!Schema::hasColumn('document_types', 'is_required')) {
                $table->boolean('is_required')->default(false)->after('expiration_buffer_days');
            }

            if (!Schema::hasColumn('document_types', 'requirement_notes')) {
                $table->text('requirement_notes')->nullable()->after('expiration_buffer_days');
            }

            if (!Schema::hasColumn('document_types', 'source_document_type_id')) {
                $table->foreignUuid('source_document_type_id')->nullable()->after('requirement_notes');
            }
        });

        Schema::table('document_types', function (Blueprint $table) {
            // Add indexes and FK after columns exist.
            if (Schema::hasColumn('document_types', 'source_document_type_id')) {
                $table->index(['employment_category', 'process_type', 'is_active'], 'idx_doc_types_category_process_active');
                $table->index('source_document_type_id', 'idx_doc_types_source_document_type_id');
                $table->foreign('source_document_type_id', 'fk_doc_types_source_document_type_id')
                    ->references('id')
                    ->on('document_types')
                    ->nullOnDelete();
            }
        });

        // Migrate old table data into document_types as rule rows if table still exists.
        if (Schema::hasTable('employment_document_requirements')) {
            $requirements = DB::table('employment_document_requirements as r')
                ->join('document_types as d', 'd.id', '=', 'r.document_type_id')
                ->select(
                    'r.document_type_id',
                    'r.employment_category',
                    'r.process_type',
                    'r.expiration_buffer_days',
                    'r.is_required',
                    'r.is_active',
                    'r.notes',
                    'r.created_at',
                    'r.updated_at',
                    'd.name',
                    'd.description',
                    'd.file_format',
                    'd.max_file_size'
                )
                ->get();

            if ($requirements->isNotEmpty()) {
                $rows = [];

                foreach ($requirements as $item) {
                    $exists = DB::table('document_types')
                        ->where('source_document_type_id', $item->document_type_id)
                        ->where('employment_category', $item->employment_category)
                        ->where('process_type', $item->process_type)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $rows[] = [
                        'id' => (string) Str::uuid(),
                        'name' => $item->name,
                        'description' => $item->description,
                        'file_format' => $item->file_format,
                        'max_file_size' => $item->max_file_size,
                        'is_active' => (bool) $item->is_active,
                        'is_universal' => false,
                        'employment_category' => $item->employment_category,
                        'process_type' => $item->process_type,
                        'expiration_buffer_days' => (int) ($item->expiration_buffer_days ?? 0),
                        'requirement_notes' => $item->notes,
                        'source_document_type_id' => $item->document_type_id,
                        'is_required' => (bool) $item->is_required,
                        'created_at' => $item->created_at ?? now(),
                        'updated_at' => $item->updated_at ?? now(),
                    ];
                }

                if (!empty($rows)) {
                    foreach (array_chunk($rows, 200) as $chunk) {
                        DB::table('document_types')->insert($chunk);
                    }
                }
            }

            Schema::dropIfExists('employment_document_requirements');
        }

        // Base document rows are master references and should not become automatic requirements.
        DB::table('document_types')
            ->whereNull('source_document_type_id')
            ->update(['is_required' => false]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('document_types')) {
            return;
        }

        if (!Schema::hasTable('employment_document_requirements')) {
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

        Schema::table('document_types', function (Blueprint $table) {
            if (Schema::hasColumn('document_types', 'source_document_type_id')) {
                $table->dropForeign('fk_doc_types_source_document_type_id');
                $table->dropIndex('idx_doc_types_source_document_type_id');
            }

            if (Schema::hasColumn('document_types', 'employment_category')) {
                $table->dropIndex('idx_doc_types_category_process_active');
            }

            foreach (['source_document_type_id', 'requirement_notes', 'is_required', 'expiration_buffer_days', 'process_type', 'employment_category'] as $column) {
                if (Schema::hasColumn('document_types', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
