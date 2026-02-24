<?php

namespace App\Services\Master;

use App\DTOs\Master\ReligionDTO;
use App\Repositories\Contracts\Master\ReligionRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReligionService
{
    public function __construct(
        private readonly ReligionRepositoryInterface $repository
    ) {}

    /**
     * Get all religions with pagination
     */
    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get all religions
     */
    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * Get all active religions
     */
    public function getAllActive()
    {
        return Cache::remember('master_religions_active', 3600, function () {
            return $this->repository->active();
        });
    }

    /**
     * Find religion by ID
     */
    public function findById(string $id)
    {
        $religion = $this->repository->findById($id);

        if (!$religion) {
            throw new \Exception('Agama tidak ditemukan');
        }

        return $religion;
    }

    /**
     * Get religion by name
     */
    public function getByName(string $name)
    {
        return $this->repository->getByName($name);
    }

    /**
     * Create new religion
     */
    public function create(ReligionDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check if name already exists
            if ($this->repository->getByName($dto->name)) {
                throw new \Exception('Nama agama sudah digunakan');
            }

            $religion = $this->repository->create($dto);

            DB::commit();
            Cache::forget('master_religions_active');

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
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing religion
     */
    public function update(string $id, ReligionDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $religion = $this->findById($id);

            // Check if name already exists (except current)
            $existingByName = $this->repository->getByName($dto->name);
            if ($existingByName && $existingByName->id !== $id) {
                throw new \Exception('Nama agama sudah digunakan');
            }

            $religion = $this->repository->update($id, $dto);

            DB::commit();
            Cache::forget('master_religions_active');

            return [
                'success' => true,
                'message' => 'Agama berhasil diperbarui',
                'data' => $religion,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating religion: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete religion
     */
    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $religion = $this->findById($id);

            // Check if religion has workers
            if ($religion->workers()->exists()) {
                throw new \Exception('Agama tidak dapat dihapus karena masih digunakan oleh pegawai');
            }

            $this->repository->delete($id);

            DB::commit();
            Cache::forget('master_religions_active');

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

    /**
     * Toggle religion status
     */
    public function toggleStatus(string $id): array
    {
        try {
            DB::beginTransaction();

            $religion = $this->repository->toggleStatus($id);

            DB::commit();
            Cache::forget('master_religions_active');

            return [
                'success' => true,
                'message' => 'Status agama berhasil diubah',
                'data' => $religion,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling religion status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search religions
     */
    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }
}
