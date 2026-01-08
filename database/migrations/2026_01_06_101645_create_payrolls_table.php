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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('period'); // format: YYYY-MM (contoh: 2026-01)
            $table->date('period_start');
            $table->date('period_end');
            $table->date('payment_date')->nullable();
            
            // Komponen gaji
            $table->decimal('basic_salary', 15, 2)->default(0); // gaji pokok
            $table->decimal('total_earnings', 15, 2)->default(0); // total pendapatan (termasuk tunjangan)
            $table->decimal('total_deductions', 15, 2)->default(0); // total potongan
            $table->decimal('gross_salary', 15, 2)->default(0); // kotor (sebelum pajak)
            $table->decimal('net_salary', 15, 2)->default(0); // bersih (setelah potongan)
            
            // Attendance data
            $table->integer('total_days_worked')->default(0);
            $table->integer('total_present')->default(0);
            $table->integer('total_late')->default(0);
            $table->integer('total_absent')->default(0);
            $table->decimal('total_overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 15, 2)->default(0);
            
            // Tax
            $table->decimal('tax_amount', 15, 2)->default(0);
            
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['worker_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
