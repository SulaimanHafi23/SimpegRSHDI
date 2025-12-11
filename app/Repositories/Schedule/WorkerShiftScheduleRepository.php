<?php
// filepath: app/Repositories/WorkerShiftScheduleRepository.php

namespace App\Repositories\Schedule;

use App\Models\WorkerShiftSchedule;
use App\Repositories\Contracts\Schedule\WorkerShiftScheduleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WorkerShiftScheduleRepository implements WorkerShiftScheduleRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly WorkerShiftSchedule $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['worker', 'shift', 'shiftPattern', 'replacedWorker']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('schedule_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('schedule_date', '<=', $filters['end_date']);
        }

        return $query
            ->orderBy('schedule_date', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['worker', 'shift', 'shiftPattern', 'replacedWorker'])
            ->findOrFail($id);
    }

    public function findByWorkerAndDate(string $workerId, string $date)
    {
        return $this->model
            ->with(['shift', 'shiftPattern'])
            ->where('worker_id', $workerId)
            ->where('schedule_date', $date)
            ->first();
    }

    public function getByWorker(string $workerId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->with(['shift', 'shiftPattern'])
            ->where('worker_id', $workerId);

        if ($startDate) {
            $query->where('schedule_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('schedule_date', '<=', $endDate);
        }

        return $query
            ->orderBy('schedule_date')
            ->get();
    }

    public function getByDate(string $date): Collection
    {
        return $this->model
            ->with(['worker', 'shift', 'shiftPattern'])
            ->where('schedule_date', $date)
            ->orderBy('shift_id')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model
            ->with(['worker', 'shift', 'shiftPattern'])
            ->whereBetween('schedule_date', [$startDate, $endDate])
            ->orderBy('schedule_date')
            ->orderBy('shift_id')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $schedule = $this->findById($id);
        return $schedule->update($data);
    }

    public function delete(string $id): bool
    {
        $schedule = $this->findById($id);
        return $schedule->delete();
    }

    public function bulkCreate(array $schedules): bool
    {
        try {
            $this->model->insert($schedules);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkConflict(string $workerId, string $date): bool
    {
        return $this->model
            ->where('worker_id', $workerId)
            ->where('schedule_date', $date)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }
}
