<?php

namespace Database\Seeders;

use App\Models\WorkerDocument;
use App\Models\Worker;
use App\Models\Master\DepartmentDocumentType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WorkerDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Worker Documents...');

        $workers = Worker::with('department')->get();

        foreach ($workers as $worker) {
            // Get document types required for this worker's department
            $documentTypes = DepartmentDocumentType::where('department_id', $worker->department_id)
                ->where('is_required', true)
                ->get();

            foreach ($documentTypes as $deptDocType) {
                // 80% chance the worker has uploaded this document
                if (rand(1, 100) <= 80) {
                    $uploadedAt = Carbon::now()->subDays(rand(1, 365));

                    Berkas::create([
                        'worker_id' => $worker->id,
                        'department_document_type_id' => $deptDocType->id,
                        'file_path' => "documents/worker_{$worker->id}/document_{$deptDocType->id}.pdf",
                        'original_name' => "{$deptDocType->documentType->name}_{$worker->name}.pdf",
                        'file_size' => rand(100000, 5000000), // 100KB - 5MB
                        'mime_type' => 'application/pdf',
                        'uploaded_at' => $uploadedAt,
                        'status' => $this->getRandomStatus(),
                        'verified_by' => null,
                        'verified_at' => null,
                        'notes' => null,
                    ]);
                }
            }

            // Also add some optional documents
            $optionalDocTypes = DepartmentDocumentType::where('department_id', $worker->department_id)
                ->where('is_required', false)
                ->get();

            foreach ($optionalDocTypes as $deptDocType) {
                // 40% chance the worker has uploaded optional documents
                if (rand(1, 100) <= 40) {
                    $uploadedAt = Carbon::now()->subDays(rand(1, 365));

                    WorkerDocument::create([
                        'worker_id' => $worker->id,
                        'department_document_type_id' => $deptDocType->id,
                        'file_path' => "documents/worker_{$worker->id}/optional_{$deptDocType->id}.pdf",
                        'original_name' => "{$deptDocType->documentType->name}_{$worker->name}.pdf",
                        'file_size' => rand(100000, 3000000),
                        'mime_type' => 'application/pdf',
                        'uploaded_at' => $uploadedAt,
                        'status' => $this->getRandomStatus(),
                        'verified_by' => null,
                        'verified_at' => null,
                        'notes' => null,
                    ]);
                }
            }
        }

        $this->command->info('✅ Worker Documents seeded successfully!');
    }

    private function getRandomStatus(): string
    {
        $statuses = ['pending', 'verified', 'rejected'];
        $weights = [30, 60, 10]; // 30% pending, 60% verified, 10% rejected

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $statuses[$index];
            }
        }

        return 'pending';
    }
}
