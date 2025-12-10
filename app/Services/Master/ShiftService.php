<?php

namespace App\Services\Master;

use App\DTOs\Master\ShiftDTO;
use App\Repositories\Contracts\Master\ShiftRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShiftService
{
    public function __construct(
        private readonly ShiftRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getActive()
    {
        return $this->repository->active();
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function create(ShiftDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Validate time range
            if (!$this->isValidTimeRange($dto->startTime, $dto->endTime)) {
                throw new \Exception('Jam selesai harus lebih besar dari jam mulai (untuk shift yang melewati tengah malam, gunakan format 24 jam)');
            }

            $shift = $this->repository->create($dto->toArray());

            DB::commit();

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

    public function update(string $id, ShiftDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Validate time range
            if (!$this->isValidTimeRange($dto->startTime, $dto->endTime)) {
                throw new \Exception('Jam selesai harus lebih besar dari jam mulai (untuk shift yang melewati tengah malam, gunakan format 24 jam)');
            }

            $updated = $this->repository->update($id, $dto->toArray());

            if (!$updated) {
                throw new \Exception('Gagal mengupdate data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Shift berhasil diupdate',
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

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $shift = $this->repository->findById($id);
            
            if ($shift->workerShiftSchedules()->exists()) {
                throw new \Exception('Shift tidak dapat dihapus karena masih digunakan dalam jadwal pegawai');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

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

    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    /**
     * Validate time range (allow overnight shifts)
     */
    private function isValidTimeRange(string $startTime, string $endTime): bool
    {
        // Allow all time ranges as shifts can be overnight
        // Example: 23:00 - 07:00 is valid
        return true;
    }

    /**
     * Calculate shift duration in hours
     */
    public function calculateShiftDuration(string $startTime, string $endTime): float
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        // If end time is before start time, add a day (overnight shift)
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        return $start->diffInHours($end, true);
    }

    /**
     * Check if time is within shift range
     */
    public function isTimeInShift(string $shiftId, string $time): bool
    {
        $shift = $this->findById($shiftId);
        
        $checkTime = Carbon::createFromFormat('H:i', $time);
        $startTime = Carbon::createFromFormat('H:i', $shift->start_time);
        $endTime = Carbon::createFromFormat('H:i', $shift->end_time);

        // Handle overnight shifts
        if ($endTime->lessThan($startTime)) {
            $endTime->addDay();
            
            if ($checkTime->lessThan($startTime)) {
                $checkTime->addDay();
            }
        }

        return $checkTime->between($startTime, $endTime);
    }
}
