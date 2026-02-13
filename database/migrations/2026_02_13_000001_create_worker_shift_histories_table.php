<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_shift_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignUuid('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('effective_from')->comment('Tanggal mulai shift ini berlaku');
            $table->date('effective_until')->nullable()->comment('Tanggal akhir shift ini berlaku (null = diubah tanpa batas)');
            $table->date('changed_at')->comment('Tanggal kapan shift ini diganti/dihapus');
            $table->string('change_reason')->default('shift_replaced')->comment('Alasan: shift_replaced, shift_updated, shift_deleted');
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['worker_id', 'effective_from']);
            $table->index(['worker_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_shift_histories');
    }
};
