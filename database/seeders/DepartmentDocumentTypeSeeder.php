<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Department;
use App\Models\DocumentType;

class DepartmentDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📋 Starting DepartmentDocumentTypeSeeder...');

        // Get all departments and base document types (non-rule rows)
        $departments = Department::all()->keyBy('code');
        $documentTypes = DocumentType::query()
            ->whereNull('source_document_type_id')
            ->get()
            ->keyBy('name');

        // Define document requirements per department
        $departmentDocuments = [
            'DKT' => [ // Dokter
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'STR (Surat Tanda Registrasi)',
                'SIP (Surat Izin Praktik)', 'CV (Curriculum Vitae)', 'Foto 3x4',
                'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'PRW' => [ // Perawat
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'STR (Surat Tanda Registrasi)',
                'SIP (Surat Izin Praktik)', 'CV (Curriculum Vitae)', 'Foto 3x4',
                'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'BDN' => [ // Bidan
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'STR (Surat Tanda Registrasi)',
                'SIP (Surat Izin Praktik)', 'CV (Curriculum Vitae)', 'Foto 3x4',
                'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'ADM' => [ // Admin
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'CV (Curriculum Vitae)',
                'Foto 3x4', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan'
            ],
            'FRM' => [ // Farmasi
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'STR (Surat Tanda Registrasi)',
                'SIP (Surat Izin Praktik)', 'CV (Curriculum Vitae)', 'Foto 3x4',
                'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'LAB' => [ // Laboratorium
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'STR (Surat Tanda Registrasi)',
                'CV (Curriculum Vitae)', 'Foto 3x4', 'BPJS Kesehatan', 
                'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'RAD' => [ // Radiologi
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'STR (Surat Tanda Registrasi)',
                'CV (Curriculum Vitae)', 'Foto 3x4', 'BPJS Kesehatan',
                'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'GIZ' => [ // Gizi
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'STR (Surat Tanda Registrasi)',
                'CV (Curriculum Vitae)', 'Foto 3x4', 'BPJS Kesehatan',
                'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'KBR' => [ // Kebersihan
                'KTP', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'CV (Curriculum Vitae)',
                'Foto 3x4', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan'
            ],
            'SEC' => [ // Keamanan/Security
                'KTP', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'CV (Curriculum Vitae)',
                'Foto 3x4', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'IT' => [ // IT & Digital
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'CV (Curriculum Vitae)',
                'Foto 3x4', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'HR' => [ // Human Resources
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'CV (Curriculum Vitae)',
                'Foto 3x4', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
            'FIN' => [ // Finance & Accounting
                'KTP', 'NPWP', 'Ijazah', 'Kartu Keluarga', 'Surat Keterangan Sehat',
                'Surat Keterangan Catatan Kepolisian (SKCK)', 'CV (Curriculum Vitae)',
                'Foto 3x4', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Sertifikat Kompetensi'
            ],
        ];

        $insertData = [];
        $totalMappings = 0;

        foreach ($departmentDocuments as $deptCode => $documents) {
            if (!isset($departments[$deptCode])) {
                $this->command->warn("⚠️  Department with code '$deptCode' not found, skipping...");
                continue;
            }

            $department = $departments[$deptCode];
            
            foreach ($documents as $docName) {
                if (!isset($documentTypes[$docName])) {
                    $this->command->warn("⚠️  Document type '$docName' not found, skipping...");
                    continue;
                }

                $documentType = $documentTypes[$docName];

                $insertData[] = [
                    'id' => Str::uuid()->toString(),
                    'department_id' => $department->id,
                    'document_type_id' => $documentType->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalMappings++;
            }
        }

        // Clear existing data to avoid conflicts
        DB::table('department_document_type')->delete();
        
        // Insert all mappings
        if (!empty($insertData)) {
            // Insert in chunks to avoid memory issues
            $chunks = array_chunk($insertData, 100);
            foreach ($chunks as $chunk) {
                DB::table('department_document_type')->insert($chunk);
            }
        }

        $this->command->info("✅ Created $totalMappings department-document type mappings");
        
        // Show summary by department
        $this->command->info("\n📊 Summary by department:");
        foreach ($departmentDocuments as $deptCode => $documents) {
            if (isset($departments[$deptCode])) {
                $deptName = $departments[$deptCode]->name;
                $docCount = count($documents);
                $this->command->info("   • $deptName ($deptCode): $docCount documents");
            }
        }
    }
}