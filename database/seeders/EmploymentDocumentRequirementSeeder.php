<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmploymentDocumentRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📎 Seeding aturan pemberkasan ke tabel document_types...');

        $baseDocumentTypes = DocumentType::query()
            ->whereNull('source_document_type_id')
            ->get()
            ->keyBy('name');

        $definitions = [
            'all' => [
                'onboarding' => ['KTP', 'NPWP', 'Kartu Keluarga', 'Foto 3x4'],
                'payroll' => ['NPWP'],
            ],
            'asn' => [
                'onboarding' => ['SK ASN', 'Ijazah', 'Surat Keterangan Catatan Kepolisian (SKCK)'],
                'promotion' => ['SK ASN', 'Sertifikat Kompetensi'],
                'payroll' => ['BPJS Kesehatan', 'BPJS Ketenagakerjaan'],
            ],
            'pppk' => [
                'onboarding' => ['SK PPPK', 'Ijazah', 'CV (Curriculum Vitae)'],
                'promotion' => ['SK PPPK', 'Sertifikat Kompetensi'],
                'payroll' => ['BPJS Kesehatan', 'BPJS Ketenagakerjaan'],
                'contract_extension' => ['SK PPPK'],
            ],
            'pppk_paruh_waktu' => [
                'onboarding' => ['SK PPPK Paruh Waktu', 'Ijazah', 'CV (Curriculum Vitae)'],
                'promotion' => ['SK PPPK Paruh Waktu'],
                'payroll' => ['BPJS Kesehatan'],
                'contract_extension' => ['SK PPPK Paruh Waktu'],
            ],
            'outsourced' => [
                'onboarding' => ['Surat Penugasan Vendor', 'Kontrak Kerja Vendor'],
                'payroll' => ['Kontrak Kerja Vendor'],
                'contract_extension' => ['Kontrak Kerja Vendor'],
            ],
            'non_asn' => [
                'onboarding' => ['Ijazah', 'CV (Curriculum Vitae)'],
                'payroll' => ['BPJS Kesehatan'],
            ],
        ];

        $rows = [];

        foreach ($definitions as $category => $processes) {
            foreach ($processes as $processType => $docNames) {
                foreach ($docNames as $docName) {
                    $docType = $baseDocumentTypes->get($docName);
                    if (!$docType) {
                        $this->command->warn('⚠️ Document type tidak ditemukan: ' . $docName);
                        continue;
                    }

                    $exists = DocumentType::query()
                        ->where('source_document_type_id', $docType->id)
                        ->where('employment_category', $category)
                        ->where('process_type', $processType)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $rows[] = [
                        'id' => Str::uuid()->toString(),
                        'name' => $docType->name,
                        'description' => $docType->description,
                        'file_format' => $docType->file_format,
                        'max_file_size' => $docType->max_file_size,
                        'source_document_type_id' => $docType->id,
                        'employment_category' => $category,
                        'process_type' => $processType,
                        'expiration_buffer_days' => in_array($processType, ['payroll', 'contract_extension'], true) ? 14 : 0,
                        'is_required' => true,
                        'is_active' => true,
                        'requirement_notes' => 'Seeded default requirement',
                        'is_universal' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($rows)) {
            DB::table('document_types')->insert($rows);
        }

        $this->command->info('✅ Aturan pemberkasan di document_types: ' . count($rows));
    }
}
