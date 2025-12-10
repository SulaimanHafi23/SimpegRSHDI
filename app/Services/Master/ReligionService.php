<?php

namespace App\Services\Master;

use App\DTOs\Master\ReligionDTO;
use App\Repositories\Contracts\Master\ReligionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReligionService
{
    public function __construct(
        private readonly ReligionRepositoryInterface $repository
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

    public function create(ReligionDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $religion = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Agama berhasil ditambahkan',
                'data' => $religion,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating religion: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan agama: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, ReligionDTO $dto): array
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
                'message' => 'Agama berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating religion: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate agama: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            // Check if religion is being used
            $religion = $this->repository->findById($id);
            
            if ($religion->workers()->exists()) {
                throw new \Exception('Agama tidak dapat dihapus karena masih digunakan oleh pegawai');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Agama berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting religion: ' . $e->getMessage());

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
