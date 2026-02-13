<?php
// filepath: app/Repositories/Attendance/AttendanceRepository.php

namespace App\Repositories\Attendance;

use App\DTOs\AttendanceDTO;
use App\Models\Attendance;
use App\Repositories\Contracts\Attendance\AttendanceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function __construct(
        protected Attendance $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with([
            'worker.shift',
            'worker.workerShifts.shift',
            'worker.shiftOverrides.shift',
            'shift',
            'location'
        ]);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('attendance_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('attendance_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'late') {
                $query->where('is_late', true);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['is_late'])) {
            $query->where('is_late', $filters['is_late']);
        }

        // Department filter (for Manager access control)
        if (!empty($filters['department_id'])) {
            $query->whereHas('worker', function($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        // Advanced search functionality
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(attendance_date) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(status) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(check_in) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(check_out) LIKE ?', ['%' . $search . '%'])
                  ->orWhereHas('location', function($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%']);
                  })
                  ->orWhereHas('worker', function($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $search . '%']);
                  });
            });
        }

        return $query->latest('attendance_date')
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }

    public function getById(string $id): ?object
    {
        return $this->model->with(['worker', 'shift', 'location', 'photos'])
            ->find($id);
    }

    public function getByWorkerId(string $workerId, array $filters = []): Collection
    {
        $query = $this->model->where('worker_id', $workerId)
            ->with(['shift', 'location']);

        if (!empty($filters['month'])) {
            $query->whereMonth('attendance_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('attendance_date', $filters['year']);
        }

        return $query->latest('attendance_date')->get();
    }

    public function getByDate(string $date, array $filters = []): Collection
    {
        return $this->model->where('attendance_date', $date)
            ->with(['worker', 'shift'])
            ->get();
    }

    public function getByWorkerAndDate(string $workerId, string $date): ?object
    {
        return $this->model->where('worker_id', $workerId)
            ->where('attendance_date', $date)
            ->with(['shift', 'location', 'photos'])
            ->first();
    }

    public function create(AttendanceDTO $dto): object
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, AttendanceDTO $dto): object
    {
        $attendance = $this->model->findOrFail($id);
        $attendance->update($dto->toArray());
        return $attendance->fresh();
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function checkIn(AttendanceDTO $dto): object
    {
        return $this->create($dto);
    }

    public function checkOut(string $id, AttendanceDTO $dto): object
    {
        return $this->update($id, $dto);
    }

    public function getTodayAttendance(string $workerId): ?object
    {
        return $this->getByWorkerAndDate($workerId, now()->format('Y-m-d'));
    }

    public function getMonthlyReport(string $workerId, int $month, int $year): Collection
    {
        return $this->model->where('worker_id', $workerId)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with(['shift'])
            ->orderBy('attendance_date')
            ->get();
    }
}
