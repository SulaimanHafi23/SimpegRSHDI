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
            $table->string('nip')->unique()->comment('Nomor Induk Pegawai');
            $table->string('name')->comment('Nama lengkap');
            $table->string('surname')->nullable()->comment('Nama belakang (opsional)');
            $table->string('frontname')->nullable()->comment('Gelar depan (Dr., Prof., dll)');
            $table->string('backname')->nullable()->comment('Gelar belakang (S.Kep, Sp.A, dll)');
            $table->string('email')->unique();
            $table->text('address')->comment('Alamat lengkap');
            $table->date('birth_date')->comment('Tanggal lahir');
            $table->string('birth_place')->comment('Tempat lahir');
            $table->foreignUuid('gender_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('religion_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('position_id')->constrained()->restrictOnDelete();
            $table->string('phone_number')->unique();
            $table->enum('status', ['Active', 'Inactive', 'Resigned'])->default('Active');
            $table->date('hire_date')->comment('Tanggal bergabung');
            $table->date('resign_date')->nullable()->comment('Tanggal resign');
            $table->string('photo_url')->nullable()->comment('URL foto profil');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'hire_date']);
            $table->index(['position_id', 'status']);
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
