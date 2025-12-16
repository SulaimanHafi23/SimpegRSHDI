<?php

namespace App\Services\Master;

use App\DTOs\Master\GenderDTO;
use App\Repositories\Contracts\Master\GenderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenderService
{
    public function __construct(
        private readonly GenderRepositoryInterface $repository
    ) {}

    /**
     * Get all genders with pagination
     */
    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get all genders
     */
    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * Get all active genders
     */
    public function getAllActive()
    {
        return $this->repository->active();
    }

    /**
     * Find gender by ID
     */
    public function findById(string $id)
    {
        $gender = $this->repository->findById($id);

        if (!$gender) {
            throw new \Exception('Gender tidak ditemukan');
        }

        return $gender;
    }

    /**
     * Get gender by name
     */
    public function getByName(string $name)
    {
        return $this->repository->getByName($name);
    }

    /**
     * Get gender by code
     */
    public function getByCode(string $code)
    {
        return $this->repository->getByCode($code);
    }

    /**
     * Create new gender
     */
    public function create(GenderDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check if name already exists
            if ($this->repository->getByName($dto->name)) {
                throw new \Exception('Nama gender sudah digunakan');
            }

            // Check if code already exists
            if ($this->repository->getByCode($dto->code)) {
                throw new \Exception('Kode gender sudah digunakan');
            }

            $gender = $this->repository->create($dto);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Gender berhasil ditambahkan',
                'data' => $gender,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating gender: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing gender
     */
    public function update(string $id, GenderDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $gender = $this->findById($id);

            // Check if name already exists (except current)
            $existingByName = $this->repository->getByName($dto->name);
            if ($existingByName && $existingByName->id !== $id) {
                throw new \Exception('Nama gender sudah digunakan');
            }

            // Check if code already exists (except current)
            $existingByCode = $this->repository->getByCode($dto->code);
            if ($existingByCode && $existingByCode->id !== $id) {
                throw new \Exception('Kode gender sudah digunakan');
            }

            $gender = $this->repository->update($id, $dto);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Gender berhasil diperbarui',
                'data' => $gender,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating gender: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete gender
     */
    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $gender = $this->findById($id);

            // Check if gender has workers
            if ($gender->workers()->exists()) {
                throw new \Exception('Gender tidak dapat dihapus karena masih digunakan oleh pegawai');
            }

            $this->repository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Gender berhasil dihapus',
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

    /**
     * Toggle gender status
     */
    public function toggleStatus(string $id): array
    {
        try {
            DB::beginTransaction();

            $gender = $this->repository->toggleStatus($id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Status gender berhasil diubah',
                'data' => $gender,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling gender status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search genders
     */
    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }
}
