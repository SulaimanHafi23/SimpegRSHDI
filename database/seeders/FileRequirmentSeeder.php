<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\FileRequirment;
use Illuminate\Database\Seeder;

class FileRequirmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all document types
        $documentTypes = DocumentType::all();

        $sortOrder = 1;

        foreach ($documentTypes as $docType) {
            FileRequirment::create([
                'position_id' => null, // null means applies to all positions
                'document_type_id' => $docType->id,
                'is_required' => in_array($docType->name, ['KTP', 'Ijazah', 'STR']), // Mark some as required
                'is_active' => true,
                'sort_order' => $sortOrder++,
                'notes' => 'Dokumen ' . $docType->name . ' untuk keperluan administrasi',
            ]);
        }
    }
}
