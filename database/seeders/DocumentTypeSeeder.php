<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            [
                'name' => 'KTP',
                'description' => 'Kartu Tanda Penduduk',
                'file_format' => 'jpg,png,pdf',
                'max_file_size' => 2048,
            ],
            [
                'name' => 'Kartu Keluarga',
                'description' => 'Kartu Keluarga (KK)',
                'file_format' => 'jpg,png,pdf',
                'max_file_size' => 2048,
            ],
            [
                'name' => 'Ijazah',
                'description' => 'Ijazah pendidikan terakhir',
                'file_format' => 'pdf',
                'max_file_size' => 5120,
            ],
            [
                'name' => 'Transkrip Nilai',
                'description' => 'Transkrip nilai pendidikan',
                'file_format' => 'pdf',
                'max_file_size' => 5120,
            ],
            [
                'name' => 'SKCK',
                'description' => 'Surat Keterangan Catatan Kepolisian',
                'file_format' => 'pdf',
                'max_file_size' => 2048,
            ],
            [
                'name' => 'Sertifikat Kompetensi',
                'description' => 'Sertifikat kompetensi/pelatihan',
                'file_format' => 'pdf',
                'max_file_size' => 3072,
            ],
            [
                'name' => 'STR',
                'description' => 'Surat Tanda Registrasi (untuk tenaga kesehatan)',
                'file_format' => 'pdf',
                'max_file_size' => 2048,
            ],
            [
                'name' => 'SIP',
                'description' => 'Surat Izin Praktik (untuk tenaga kesehatan)',
                'file_format' => 'pdf',
                'max_file_size' => 2048,
            ],
            [
                'name' => 'Surat Keterangan Sehat',
                'description' => 'Surat keterangan sehat dari dokter',
                'file_format' => 'pdf',
                'max_file_size' => 2048,
            ],
            [
                'name' => 'Pas Foto',
                'description' => 'Pas foto 4x6',
                'file_format' => 'jpg,png',
                'max_file_size' => 1024,
            ],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::create($type);
        }
    }
}
