<?php

namespace App\Services\Master;

use App\DTOs\Master\LeaveTypeDTO;
use App\Repositories\Contracts\Master\LeaveTypeRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveTypeService
{
    public function __construct(
        private readonly LeaveTypeRepositoryInterface $repository
    ) {}

    /**
     * Get all leave types with pagination
     */
    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get all leave types
     */
    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * Get all active leave types
     */
    public function getAllActive()
    {
        return $this->repository->active();
    }

    /**
     * Find leave type by ID
     */
    public function findById(string $id)
    {
        $leaveType = $this->repository->findById($id);

        if (!$leaveType) {
            throw new \Exception('Tipe cuti tidak ditemukan');
        }

        return $leaveType;
    }

    /**
     * Get leave type by name
     */
    public function getByName(string $name)
    {
        return $this->repository->getByName($name);
    }

    /**
     * Get leave type by code
     */
    public function getByCode(string $code)
    {
        return $this->repository->getByCode($code);
    }

    /**
     * Create new leave type
     */
    public function create(LeaveTypeDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check if name already exists
            if ($this->repository->getByName($dto->name)) {
                throw new \Exception('Nama tipe cuti sudah digunakan');
            }

            // Check if code already exists
            if ($this->repository->getByCode($dto->code)) {
                throw new \Exception('Kode tipe cuti sudah digunakan');
            }

            $leaveType = $this->repository->create($dto);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tipe cuti berhasil ditambahkan',
                'data' => $leaveType,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating leave type: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing leave type
     */
    public function update(string $id, LeaveTypeDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $leaveType = $this->findById($id);

            // Check if name already exists (except current)
            $existingByName = $this->repository->getByName($dto->name);
            if ($existingByName && $existingByName->id !== $id) {
                throw new \Exception('Nama tipe cuti sudah digunakan');
            }

            // Check if code already exists (except current)
            $existingByCode = $this->repository->getByCode($dto->code);
            if ($existingByCode && $existingByCode->id !== $id) {
                throw new \Exception('Kode tipe cuti sudah digunakan');
            }

            $leaveType = $this->repository->update($id, $dto);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tipe cuti berhasil diperbarui',
                'data' => $leaveType,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating leave type: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete leave type
     */
    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $leaveType = $this->findById($id);

            // Check if leave type has leave requests
            if ($leaveType->leaveRequests()->exists()) {
                throw new \Exception('Tipe cuti tidak dapat dihapus karena masih digunakan');
            }

            $this->repository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tipe cuti berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting leave type: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Toggle leave type status
     */
    public function toggleStatus(string $id): array
    {
        try {
            DB::beginTransaction();

            $leaveType = $this->repository->toggleStatus($id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Status tipe cuti berhasil diubah',
                'data' => $leaveType,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling leave type status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search leave types
     */
    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    /**
     * Get paid leave types
     */
    public function getPaidLeaveTypes()
    {
        return $this->repository->getPaidLeaveTypes();
    }

    /**
     * Get leave types requiring approval
     */
    public function getRequiringApproval()
    {
        return $this->repository->getRequiringApproval();
    }
}
