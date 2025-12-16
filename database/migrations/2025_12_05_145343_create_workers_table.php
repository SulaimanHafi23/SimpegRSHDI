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
        Schema::create('workers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nip')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->text('address')->nullable();
            $table->date('birth_date');
            $table->string('birth_place');
            $table->foreignUuid('gender_id')->constrained('genders')->cascadeOnDelete();
            $table->foreignUuid('religion_id')->constrained('religions')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->date('hire_date');
            $table->date('resign_date')->nullable();
            $table->enum('employment_status', ['permanent', 'contract', 'internship'])->default('contract');
            $table->enum('status', ['active', 'inactive', 'resigned'])->default('active');
            $table->string('photo_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'employment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
