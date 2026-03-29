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
        Schema::dropIfExists('salary_components');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['earning', 'deduction', 'adjustment'])->default('earning');
            $table->text('description')->nullable();
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('code');
            $table->index('type');
            $table->index('is_active');
        });
    }
};
