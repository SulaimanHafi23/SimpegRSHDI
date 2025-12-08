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
        Schema::create('business_trips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('title');
            $table->string('destination');
            $table->text('destination_address');
            $table->decimal('destination_latitude', 10, 8)->nullable();
            $table->decimal('destination_longitude', 11, 8)->nullable();
            $table->text('purpose');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->string('transport_type');
            $table->enum('status', [
                'Pending', 
                'Approved', 
                'Rejected', 
                'Ongoing', 
                'Completed', 
                'Cancelled'
            ])->default('Pending');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_url')->nullable();
            $table->timestamps();

            $table->index(['worker_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trips');
    }
};
