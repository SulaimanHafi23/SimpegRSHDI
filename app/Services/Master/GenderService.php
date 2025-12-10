<?php

namespace App\Services\Master;

use App\DTOs\Master\GenderDTO;
use App\Repositories\Contracts\Master\GenderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenderService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly GenderRepositoryInterface $repository
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

    public function create(GenderDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $gender = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jenis kelamin berhasil ditambahkan',
                'data' => $gender,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating gender: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan jenis kelamin: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, GenderDTO $dto): array
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
                'message' => 'Jenis kelamin berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating gender: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate jenis kelamin: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $gender = $this->repository->findById($id);
            
            if ($gender->workers()->exists()) {
                throw new \Exception('Jenis kelamin tidak dapat dihapus karena masih digunakan oleh pegawai');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jenis kelamin berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting gender: ' . $e->getMessage());

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
