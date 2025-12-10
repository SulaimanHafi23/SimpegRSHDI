<?php

namespace App\Services\Master;

use App\DTOs\Master\PositionDTO;
use App\Repositories\Contracts\Master\PositionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositionService
{
    public function __construct(
        private readonly PositionRepositoryInterface $repository
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

    public function create(PositionDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $position = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jabatan berhasil ditambahkan',
                'data' => $position,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating position: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan jabatan: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, PositionDTO $dto): array
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
                'message' => 'Jabatan berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating position: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate jabatan: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $position = $this->repository->findById($id);
            
            if ($position->workers()->exists()) {
                throw new \Exception('Jabatan tidak dapat dihapus karena masih digunakan oleh pegawai');
            }

            if ($position->fileRequirments()->exists()) {
                throw new \Exception('Jabatan tidak dapat dihapus karena memiliki persyaratan dokumen');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jabatan berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting position: ' . $e->getMessage());

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
