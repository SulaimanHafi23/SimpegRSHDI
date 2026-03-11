<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->date('date');
            $table->text('description')->nullable();
            $table->boolean('is_national')->default(true);
            $table->timestamps();

            $table->index('date');
            $table->index(['is_national', 'date'], 'holidays_national_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
