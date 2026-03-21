<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📄 Starting DocumentTypeSeeder...');

        $documentTypes = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'KTP',
                'description' => 'Kartu Tanda Penduduk',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 2048,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'NPWP',
                'description' => 'Nomor Pokok Wajib Pajak',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 2048,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Ijazah',
                'description' => 'Ijazah Pendidikan Terakhir',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Kartu Keluarga',
                'description' => 'Kartu Keluarga (KK)',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 2048,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Surat Keterangan Sehat',
                'description' => 'Surat Keterangan Sehat dari Dokter',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 2048,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Surat Keterangan Catatan Kepolisian (SKCK)',
                'description' => 'SKCK dari Kepolisian',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 2048,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Sertifikat Kompetensi',
                'description' => 'Sertifikat Kompetensi/Keahlian Profesional',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'STR (Surat Tanda Registrasi)',
                'description' => 'STR untuk Tenaga Kesehatan',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'SIP (Surat Izin Praktik)',
                'description' => 'SIP untuk Tenaga Kesehatan',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'CV (Curriculum Vitae)',
                'description' => 'Riwayat Hidup',
                'file_format' => 'pdf,doc,docx',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Foto 3x4',
                'description' => 'Pas Foto ukuran 3x4',
                'file_format' => 'jpg,jpeg,png',
                'max_file_size' => 1024,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'BPJS Kesehatan',
                'description' => 'Kartu BPJS Kesehatan',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 2048,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'BPJS Ketenagakerjaan',
                'description' => 'Kartu BPJS Ketenagakerjaan',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 2048,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'SK ASN',
                'description' => 'Surat Keputusan Pengangkatan ASN',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'SK PPPK',
                'description' => 'Surat Keputusan Pengangkatan PPPK',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'SK PPPK Paruh Waktu',
                'description' => 'Surat Keputusan Pengangkatan PPPK Paruh Waktu',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Surat Penugasan Vendor',
                'description' => 'Surat penugasan tenaga kerja dari vendor outsourcing',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Kontrak Kerja Vendor',
                'description' => 'Kontrak kerja antara rumah sakit dan vendor outsourcing',
                'file_format' => 'pdf,jpg,jpeg,png',
                'max_file_size' => 5120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('document_types')->insert($documentTypes);

        $this->command->info('✅ Created ' . count($documentTypes) . ' document types');
    }
}
