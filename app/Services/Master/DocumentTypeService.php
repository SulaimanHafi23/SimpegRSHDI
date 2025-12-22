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

    /**
     * Get all document types with pagination
     */
    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get all document types with filters
     */
    public function getAll(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Get all active document types
     */
    public function getAllActive()
    {
        return $this->repository->active();
    }

    /**
     * Alias for getAllActive
     */
    public function getActive()
    {
        return $this->getAllActive();
    }

    /**
     * Get all required document types
     */
    public function getRequired()
    {
        return $this->repository->required();
    }

    /**
     * Find document type by ID
     */
    public function findById(string $id)
    {
        $documentType = $this->repository->findById($id);

        if (!$documentType) {
            throw new \Exception('Tipe dokumen tidak ditemukan');
        }

        return $documentType;
    }

    /**
     * Get document type by name
     */
    public function getByName(string $name)
    {
        return $this->repository->getByName($name);
    }

    /**
     * Get document type by code
     */
    public function getByCode(string $code)
    {
        return $this->repository->getByCode($code);
    }

    /**
     * Create new document type
     */
    public function create(DocumentTypeDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check if name already exists
            if ($this->repository->getByName($dto->name)) {
                throw new \Exception('Nama tipe dokumen sudah digunakan');
            }

            // Check if code already exists
            if ($this->repository->getByCode($dto->code)) {
                throw new \Exception('Kode tipe dokumen sudah digunakan');
            }

            $documentType = $this->repository->create($dto);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tipe dokumen berhasil ditambahkan',
                'data' => $documentType,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating document type: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing document type
     */
    public function update(string $id, DocumentTypeDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $documentType = $this->findById($id);

            // Check if name already exists (except current)
            $existingByName = $this->repository->getByName($dto->name);
            if ($existingByName && $existingByName->id !== $id) {
                throw new \Exception('Nama tipe dokumen sudah digunakan');
            }

            // Check if code already exists (except current)
            $existingByCode = $this->repository->getByCode($dto->code);
            if ($existingByCode && $existingByCode->id !== $id) {
                throw new \Exception('Kode tipe dokumen sudah digunakan');
            }

            $documentType = $this->repository->update($id, $dto);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tipe dokumen berhasil diperbarui',
                'data' => $documentType,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating document type: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete document type
     */
    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $documentType = $this->findById($id);

            // Check if document type has worker documents
            if ($documentType->workerDocuments()->exists()) {
                throw new \Exception('Tipe dokumen tidak dapat dihapus karena masih digunakan');
            }

            $this->repository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tipe dokumen berhasil dihapus',
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

    /**
     * Toggle document type status
     */
    public function toggleStatus(string $id): array
    {
        try {
            DB::beginTransaction();

            $documentType = $this->repository->toggleStatus($id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Status tipe dokumen berhasil diubah',
                'data' => $documentType,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling document type status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search document types
     */
    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    /**
     * Validate file extension
     */
    public function isValidExtension(string $documentTypeId, string $extension): bool
    {
        $documentType = $this->findById($documentTypeId);

        if (!$documentType->allowed_extensions) {
            return true; // All extensions allowed
        }

        $allowedExtensions = explode(',', $documentType->allowed_extensions);
        $allowedExtensions = array_map('trim', $allowedExtensions);

        return in_array(strtolower($extension), array_map('strtolower', $allowedExtensions));
    }

    /**
     * Validate file size
     */
    public function isValidFileSize(string $documentTypeId, int $fileSize): bool
    {
        $documentType = $this->findById($documentTypeId);

        if (!$documentType->max_file_size) {
            return true; // No size limit
        }

        // Convert max_file_size from KB to bytes
        $maxSizeInBytes = $documentType->max_file_size * 1024;

        return $fileSize <= $maxSizeInBytes;
    }
}
