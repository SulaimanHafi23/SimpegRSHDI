<?php

namespace App\Services\Master;

use App\DTOs\Master\ShiftDTO;
use App\Repositories\Contracts\Master\ShiftRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShiftService
{
    public function __construct(
        private readonly ShiftRepositoryInterface $repository
    ) {}

    /**
     * Get all shifts with pagination
     */
    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get all shifts
     */
    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * Get active shifts
     */
    public function getActive()
    {
        return Cache::remember('master_shifts_active', 3600, function () {
            return $this->repository->active();
        });
    }

    /**
     * Find shift by ID
     */
    public function findById(string $id)
    {
        $shift = $this->repository->findById($id);

        if (!$shift) {
            throw new \Exception('Shift tidak ditemukan');
        }

        return $shift;
    }

    /**
     * Get shift by name
     */
    public function getByName(string $name)
    {
        return $this->repository->getByName($name);
    }

    /**
     * Create new shift
     */
    public function create(ShiftDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Validate time range
            if (!$this->isValidTimeRange($dto->start_time, $dto->end_time)) {
                throw new \Exception('Jam selesai harus lebih besar dari jam mulai');
            }

            // Check if shift name already exists
            if ($this->repository->getByName($dto->name)) {
                throw new \Exception('Nama shift sudah digunakan');
            }

            $shift = $this->repository->create($dto);

            DB::commit();
            Cache::forget('master_shifts_active');

            return [
                'success' => true,
                'message' => 'Shift berhasil ditambahkan',
                'data' => $shift,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating shift: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing shift
     */
    public function update(string $id, ShiftDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $shift = $this->findById($id);

            // Validate time range
            if (!$this->isValidTimeRange($dto->start_time, $dto->end_time)) {
                throw new \Exception('Jam selesai harus lebih besar dari jam mulai');
            }

            // Check if name already exists (except current)
            $existingByName = $this->repository->getByName($dto->name);
            if ($existingByName && $existingByName->id !== $id) {
                throw new \Exception('Nama shift sudah digunakan');
            }

            $shift = $this->repository->update($id, $dto);

            DB::commit();
            Cache::forget('master_shifts_active');

            return [
                'success' => true,
                'message' => 'Shift berhasil diperbarui',
                'data' => $shift,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating shift: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete shift
     */
    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $shift = $this->findById($id);

            // Check if shift has worker shifts
            if ($shift->workerShifts()->exists()) {
                throw new \Exception('Shift tidak dapat dihapus karena masih digunakan oleh pegawai');
            }

            $this->repository->delete($id);

            DB::commit();
            Cache::forget('master_shifts_active');

            return [
                'success' => true,
                'message' => 'Shift berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting shift: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Toggle shift status
     */
    public function toggleStatus(string $id): array
    {
        try {
            DB::beginTransaction();

            $shift = $this->repository->toggleStatus($id);

            DB::commit();
            Cache::forget('master_shifts_active');

            return [
                'success' => true,
                'message' => 'Status shift berhasil diubah',
                'data' => $shift,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling shift status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search shifts
     */
    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    /**
     * Get overnight shifts
     */
    public function getOvernightShifts()
    {
        return $this->repository->getOvernightShifts();
    }

    /**
     * Validate time range
     */
    private function isValidTimeRange(string $startTime, string $endTime): bool
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        // If end time is less than start time, it's an overnight shift
        if ($end->lessThan($start)) {
            return true; // Overnight shift is valid
        }

        return $end->greaterThan($start);
    }

    /**
     * Calculate shift duration in hours
     */
    public function calculateShiftDuration(string $startTime, string $endTime): float
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        if ($end->lessThan($start)) {
            // Overnight shift
            $end->addDay();
        }

        return $start->diffInHours($end, true);
    }

    /**
     * Check if time is within shift
     */
    public function isTimeInShift(string $shiftId, string $time): bool
    {
        $shift = $this->findById($shiftId);
        $checkTime = Carbon::createFromFormat('H:i', $time);
        $startTime = Carbon::createFromFormat('H:i', $shift->start_time);
        $endTime = Carbon::createFromFormat('H:i', $shift->end_time);

        if ($shift->is_overnight) {
            // For overnight shifts
            return $checkTime->greaterThanOrEqualTo($startTime) ||
                   $checkTime->lessThanOrEqualTo($endTime);
        }

        return $checkTime->between($startTime, $endTime);
    }

    /**
     * Get shift statistics
     */
    public function getShiftStatistics(string $id): array
    {
        $shift = $this->findById($id);

        return [
            'total_workers' => $shift->workerShifts()->count(),
            'active_workers' => $shift->workerShifts()->where('is_active', true)->count(),
            'total_attendances_today' => $shift->attendances()
                ->whereDate('attendance_date', now())
                ->count(),
        ];
    }
}
