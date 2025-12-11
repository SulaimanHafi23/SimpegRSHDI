<?php

namespace App\Repositories;

use App\Models\Absent;
use App\Repositories\Contracts\AbsentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class AbsentRepository implements AbsentRepositoryInterface
{
    public function __construct(
        private readonly Absent $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['worker.position', 'location']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('check_in', $filters['date']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('check_in', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('check_in', '<=', $filters['end_date']);
        }

        return $query
            ->orderBy('check_in', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['worker.position', 'location'])
            ->findOrFail($id);
    }

    public function findByWorkerAndDate(string $workerId, string $date)
    {
        return $this->model
            ->where('worker_id', $workerId)
            ->whereDate('check_in', $date)
            ->first();
    }

    public function getByWorker(string $workerId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->with(['location'])
            ->where('worker_id', $workerId);

        if ($startDate) {
            $query->whereDate('check_in', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('check_in', '<=', $endDate);
        }

        return $query
            ->orderBy('check_in', 'desc')
            ->get();
    }

    public function getByDate(string $date): Collection
    {
        return $this->model
            ->with(['worker.position', 'location'])
            ->whereDate('check_in', $date)
            ->orderBy('check_in')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model
            ->with(['worker.position', 'location'])
            ->whereDate('check_in', '>=', $startDate)
            ->whereDate('check_in', '<=', $endDate)
            ->orderBy('check_in', 'desc')
            ->get();
    }

    public function getTodayAbsents(): Collection
    {
        return $this->model
            ->with(['worker.position', 'location'])
            ->whereDate('check_in', Carbon::today())
            ->orderBy('check_in')
            ->get();
    }

    public function getLateToday(): Collection
    {
        return $this->model
            ->with(['worker.position', 'location'])
            ->whereDate('date', Carbon::today())
            ->where('status', 'late')
            ->orderBy('check_in')
            ->get();
    }

    public function getAbsentToday(): Collection
    {
        return $this->model
            ->with(['worker.position'])
            ->whereDate('date', Carbon::today())
            ->where('status', 'absent')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $absent = $this->findById($id);
        return $absent->update($data);
    }

    public function delete(string $id): bool
    {
        $absent = $this->findById($id);
        return $absent->delete();
    }

    public function checkOut(string $id, array $data): bool
    {
        return $this->update($id, [
            'check_out' => $data['check_out'],
            'check_out_photo' => $data['check_out_photo'] ?? null,
            'check_out_latitude' => $data['check_out_latitude'] ?? null,
            'check_out_longitude' => $data['check_out_longitude'] ?? null,
        ]);
    }
}
