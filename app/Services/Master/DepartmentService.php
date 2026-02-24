<?php

namespace App\Services\Master;

use App\DTOs\Master\DepartmentDTO;
use App\Repositories\Contracts\Master\DepartmentRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentService
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $repository
    ) {}

    /**
     * Get all departments with pagination
     */
    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get all departments with filters
     */
    public function getAll(array $filters = [])
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Get all active departments
     */
    public function getAllActive()
    {
        return Cache::remember('master_departments_active', 3600, function () {
            return $this->repository->active();
        });
    }

    /**
     * Find department by ID
     */
    public function findById(string $id)
    {
        $department = $this->repository->findById($id);

        if (!$department) {
            throw new \Exception('Department tidak ditemukan');
        }

        return $department;
    }

    /**
     * Get department by code
     */
    public function getByCode(string $code)
    {
        return $this->repository->getByCode($code);
    }

    /**
     * Get department by name
     */
    public function getByName(string $name)
    {
        return $this->repository->getByName($name);
    }

    /**
     * Create new department
     */
    public function create(DepartmentDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check if name already exists
            if ($this->repository->getByName($dto->name)) {
                throw new \Exception('Nama department sudah digunakan');
            }

            // Check if code already exists
            if ($this->repository->getByCode($dto->code)) {
                throw new \Exception('Kode department sudah digunakan');
            }

            // Check if parent exists
            if ($dto->id && !$this->repository->findById($dto->id)) {
                throw new \Exception('Parent department tidak ditemukan');
            }

            $department = $this->repository->create($dto);

            DB::commit();
            Cache::forget('master_departments_active');

            return [
                'success' => true,
                'message' => 'Department berhasil ditambahkan',
                'data' => $department,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating department: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing department
     */
    public function update(string $id, DepartmentDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $department = $this->findById($id);

            // Check if name already exists (except current)
            $existingByName = $this->repository->getByName($dto->name);
            if ($existingByName && $existingByName->id !== $id) {
                throw new \Exception('Nama department sudah digunakan');
            }

            // Check if code already exists (except current)
            $existingByCode = $this->repository->getByCode($dto->code);
            if ($existingByCode && $existingByCode->id !== $id) {
                throw new \Exception('Kode department sudah digunakan');
            }

            // Check if parent exists
            if ($dto->id && !$this->repository->findById($dto->id)) {
                throw new \Exception('Parent department tidak ditemukan');
            }

            // Prevent setting itself as parent
            if ($dto->id === $id) {
                throw new \Exception('Department tidak bisa menjadi parent dari dirinya sendiri');
            }

            $department = $this->repository->update($id, $dto);

            DB::commit();
            Cache::forget('master_departments_active');

            return [
                'success' => true,
                'message' => 'Department berhasil diperbarui',
                'data' => $department,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating department: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete department
     */
    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $department = $this->findById($id);

            // Check if department has workers
            if ($department->workers()->exists()) {
                throw new \Exception('Department tidak dapat dihapus karena masih memiliki pegawai');
            }

            // Check if department has children
            if ($department->children()->exists()) {
                throw new \Exception('Department tidak dapat dihapus karena masih memiliki sub-department');
            }

            $this->repository->delete($id);

            DB::commit();
            Cache::forget('master_departments_active');

            return [
                'success' => true,
                'message' => 'Department berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting department: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Toggle department status
     */
    public function toggleStatus(string $id): array
    {
        try {
            DB::beginTransaction();

            $department = $this->repository->toggleStatus($id);

            DB::commit();
            Cache::forget('master_departments_active');

            return [
                'success' => true,
                'message' => 'Status department berhasil diubah',
                'data' => $department,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling department status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search departments
     */
    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    /**
     * Get department hierarchy
     */
    public function getHierarchy()
    {
        return $this->repository->getHierarchy();
    }

    /**
     * Get child departments
     */
    public function getChildren(string $parentId)
    {
        return $this->repository->getChildren($parentId);
    }
}