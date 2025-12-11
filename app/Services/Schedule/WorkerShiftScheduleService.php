<?php

namespace App\Services\Schedule;

use App\DTOs\WorkerShiftScheduleDTO;
use App\Repositories\Contracts\Schedule\WorkerShiftScheduleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WorkerShiftScheduleService
{
    public function __construct(
        private readonly WorkerShiftScheduleRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function getWorkerSchedule(string $workerId, ?string $startDate = null, ?string $endDate = null)
    {
        return $this->repository->getByWorker($workerId, $startDate, $endDate);
    }

    public function getScheduleByDate(string $date)
    {
        return $this->repository->getByDate($date);
    }

    public function create(WorkerShiftScheduleDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check for schedule conflict
            if ($this->repository->checkConflict($dto->workerId, $dto->scheduleDate)) {
                throw new \Exception('Pegawai sudah memiliki jadwal pada tanggal tersebut');
            }

            $schedule = $this->repository->create($dto->toArray());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jadwal shift berhasil ditambahkan',
                'data' => $schedule,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating schedule: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, WorkerShiftScheduleDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $schedule = $this->repository->findById($id);

            // Check if date/worker changed and has conflict
            if ($schedule->worker_id !== $dto->workerId || 
                $schedule->schedule_date !== $dto->scheduleDate) {
                
                $hasConflict = $this->repository->checkConflict($dto->workerId, $dto->scheduleDate);
                
                if ($hasConflict) {
                    $existing = $this->repository->findByWorkerAndDate($dto->workerId, $dto->scheduleDate);
                    if ($existing && $existing->id !== $id) {
                        throw new \Exception('Pegawai sudah memiliki jadwal pada tanggal tersebut');
                    }
                }
            }

            $updated = $this->repository->update($id, $dto->toArray());

            if (!$updated) {
                throw new \Exception('Gagal mengupdate data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jadwal shift berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating schedule: ' . $e->getMessage());

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

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jadwal shift berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting schedule: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate schedule for multiple workers
     */
    public function generateSchedule(
        array $workerIds,
        string $shiftId,
        string $shiftPatternId,
        string $startDate,
        string $endDate
    ): array {
        try {
            DB::beginTransaction();

            $schedules = [];
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            foreach ($workerIds as $workerId) {
                $current = $start->copy();
                
                while ($current->lte($end)) {
                    // Check if worker already has schedule on this date
                    if (!$this->repository->checkConflict($workerId, $current->format('Y-m-d'))) {
                        $schedules[] = [
                            'id' => Str::uuid()->toString(),
                            'worker_id' => $workerId,
                            'shift_id' => $shiftId,
                            'shift_pattern_id' => $shiftPatternId,
                            'schedule_date' => $current->format('Y-m-d'),
                            'status' => 'scheduled',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    
                    $current->addDay();
                }
            }

            if (empty($schedules)) {
                throw new \Exception('Tidak ada jadwal yang dapat dibuat. Semua pegawai sudah memiliki jadwal pada rentang tanggal tersebut.');
            }

            $this->repository->bulkCreate($schedules);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jadwal berhasil di-generate untuk ' . count($schedules) . ' shift',
                'data' => [
                    'total_schedules' => count($schedules),
                    'workers_count' => count($workerIds),
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating schedule: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Swap shift between two workers
     */
    public function swapShift(string $scheduleId1, string $scheduleId2): array
    {
        try {
            DB::beginTransaction();

            $schedule1 = $this->repository->findById($scheduleId1);
            $schedule2 = $this->repository->findById($scheduleId2);

            // Swap workers
            $this->repository->update($scheduleId1, [
                'worker_id' => $schedule2->worker_id,
                'replaced_worker_id' => $schedule1->worker_id,
                'status' => 'swapped',
            ]);

            $this->repository->update($scheduleId2, [
                'worker_id' => $schedule1->worker_id,
                'replaced_worker_id' => $schedule2->worker_id,
                'status' => 'swapped',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Shift berhasil ditukar',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error swapping shifts: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
