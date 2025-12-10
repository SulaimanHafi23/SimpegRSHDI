<?php

namespace App\Services\Master;

use App\DTOs\Master\DocumentTypeDTO;
use App\Repositories\Contracts\Master\DocumentTypeRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentTypeService
{
    public function __construct(
        private readonly DocumentTypeRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function findWithFileRequirements(string $id)
    {
        return $this->repository->withFileRequirements($id);
    }

    public function create(DocumentTypeDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $documentType = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jenis dokumen berhasil ditambahkan',
                'data' => $documentType,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating document type: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan jenis dokumen: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, DocumentTypeDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $updated = $this->repository->update($id, $dto->toArray());

            if (!$updated) {
                throw new \Exception('Gagal mengupdate data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jenis dokumen berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating document type: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate jenis dokumen: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $documentType = $this->repository->findById($id);
            
            if ($documentType->fileRequirments()->exists()) {
                throw new \Exception('Jenis dokumen tidak dapat dihapus karena masih digunakan dalam persyaratan dokumen');
            }

            if ($documentType->berkas()->exists()) {
                throw new \Exception('Jenis dokumen tidak dapat dihapus karena masih ada dokumen yang menggunakan jenis ini');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jenis dokumen berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting document type: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    /**
     * Parse file format string to array
     */
    public function parseFileFormats(string $fileFormat): array
    {
        return array_map('trim', explode(',', strtolower($fileFormat)));
    }

    /**
     * Validate if file extension is allowed
     */
    public function isValidFileExtension(string $documentTypeId, string $extension): bool
    {
        $documentType = $this->findById($documentTypeId);
        $allowedFormats = $this->parseFileFormats($documentType->file_format);
        
        return in_array(strtolower($extension), $allowedFormats);
    }
}
