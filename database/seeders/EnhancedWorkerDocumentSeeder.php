<?php

namespace Database\Seeders;

use App\Models\WorkerDocument;
use App\Models\Worker;
use App\Models\DocumentType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnhancedWorkerDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds dengan berbagai status
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive worker document data...');

        $workers = Worker::all();
        $documentTypes = DocumentType::all();

        if ($workers->isEmpty() || $documentTypes->isEmpty()) {
            $this->command->warn('No workers or document types found!');
            return;
        }

        $statuses = [
            'pending' => 25,      // 25% pending verification
            'verified' => 60,     // 60% verified
            'rejected' => 15,     // 15% rejected
        ];

        foreach ($workers as $worker) {
            // Setiap worker punya 3-8 dokumen
            $docCount = rand(3, 8);
            $usedTypes = [];

            for ($i = 0; $i < $docCount; $i++) {
                // Avoid duplicate document types per worker
                $availableTypes = $documentTypes->whereNotIn('id', $usedTypes);
                
                if ($availableTypes->isEmpty()) {
                    break;
                }

                $documentType = $availableTypes->random();
                $usedTypes[] = $documentType->id;

                $status = $this->getRandomStatus($statuses);
                
                $doc = $this->createWorkerDocument($worker, $documentType, $status);

                $this->command->info("Created {$status} document for {$worker->name}: {$documentType->name}");
            }
        }

        $this->command->info('✅ Enhanced worker document data created successfully!');
    }

    /**
     * Get random status berdasarkan weight
     */
    private function getRandomStatus(array $statuses): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($statuses as $status => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'pending';
    }

    /**
     * Create worker document dengan detail
     */
    private function createWorkerDocument(Worker $worker, DocumentType $documentType, string $status)
    {
        $now = Carbon::now();

        // Generate file name
        $extensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $extension = $extensions[array_rand($extensions)];
        $fileName = strtolower(str_replace(' ', '_', $documentType->name)) . '_' . Str::random(8) . '.' . $extension;
        $filePath = 'worker_documents/' . $worker->id . '/' . $fileName;

        // Generate random file size (100KB - 5MB)
        $fileSize = rand(100000, 5000000);

        $data = [
            'id' => Str::uuid(),
            'worker_id' => $worker->id,
            'document_type_id' => $documentType->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'status' => $status,
            'uploaded_at' => $now->copy()->subDays(rand(1, 90)),
        ];

        // Add notes (40% kemungkinan)
        if (rand(1, 100) <= 40) {
            $notes = [
                'Dokumen lengkap dan jelas',
                'Dokumen original',
                'Fotocopy legalisir',
                'Scan dengan kualitas bagus',
                'Dokumen masih berlaku',
            ];
            $data['notes'] = $notes[array_rand($notes)];
        }

        // Add verification details
        if (in_array($status, ['verified', 'rejected'])) {
            $verifier = \App\Models\User::role(['HR', 'Super Admin'])->inRandomOrder()->first();
            
            if ($verifier) {
                $data['verified_by'] = $verifier->id;
                $data['verified_at'] = $data['uploaded_at']->copy()->addDays(rand(1, 5));
            }

            if ($status === 'rejected') {
                $rejectionReasons = [
                    'Dokumen tidak jelas/blur',
                    'Dokumen sudah kadaluarsa',
                    'Bukan dokumen original atau legalisir',
                    'Informasi tidak lengkap',
                    'Format file tidak sesuai',
                    'Ukuran file terlalu besar',
                    'Dokumen tidak sesuai dengan yang diminta',
                ];
                $data['rejection_reason'] = $rejectionReasons[array_rand($rejectionReasons)];
            } else {
                $data['verification_notes'] = 'Dokumen telah diverifikasi dan sesuai';
            }
        }

        // Add expiry date untuk dokumen tertentu (50% kemungkinan)
        $documentsWithExpiry = ['KTP', 'SIM', 'Passport', 'BPJS', 'License', 'Certificate'];
        $needsExpiry = false;
        foreach ($documentsWithExpiry as $expDoc) {
            if (stripos($documentType->name, $expDoc) !== false) {
                $needsExpiry = true;
                break;
            }
        }

        if ($needsExpiry || rand(1, 100) <= 50) {
            // Expiry date: 6 bulan - 5 tahun dari sekarang
            $data['expiry_date'] = $now->copy()->addMonths(rand(6, 60));
        }

        return WorkerDocument::create($data);
    }
}
