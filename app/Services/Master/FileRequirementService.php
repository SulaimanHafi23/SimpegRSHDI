<?php

namespace App\Services\Master;

use App\DTOs\Master\FileRequirementDTO;
use App\Repositories\Contracts\Master\FileRequirementRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FileRequirementService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly FileRequirementRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15, ?string $positionId = null)
    {
        return $this->repository->paginate($perPage, $positionId);
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function getByPosition(string $positionId)
    {
        return $this->repository->findByPosition($positionId);
    }

    public function getMandatoryByPosition(string $positionId)
    {
        return $this->repository->findMandatoryByPosition($positionId);
    }

    public function create(FileRequirementDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $fileRequirement = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Persyaratan dokumen berhasil ditambahkan',
                'data' => $fileRequirement,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating file requirement: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan persyaratan dokumen: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, FileRequirementDTO $dto): array
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
                'message' => 'Persyaratan dokumen berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating file requirement: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate persyaratan dokumen: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Persyaratan dokumen berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting file requirement: ' . $e->getMessage());

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
}
