<?php

// filepath: app/Repositories/BusinessTrip/BusinessTripRepository.php

namespace App\Repositories\BusinessTrip;

use App\Models\BusinessTrip;
use App\Repositories\Contracts\BusinessTrip\BusinessTripRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class BusinessTripRepository implements BusinessTripRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly BusinessTrip $model
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['worker.position', 'approver']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        return $query
            ->orderBy('start_date', 'desc')
            ->paginate($perPage);
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['worker.position', 'approver', 'reports'])
            ->findOrFail($id);
    }

    public function getByWorker(string $workerId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model
            ->where('worker_id', $workerId);

        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('end_date', '<=', $endDate);
        }

        return $query
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model
            ->with(['worker.position'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getActive(): Collection
    {
        return $this->model
            ->with(['worker.position'])
            ->where('status', 'approved')
            ->where('start_date', '<=', Carbon::today())
            ->where('end_date', '>=', Carbon::today())
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $businessTrip = $this->findById($id);
        return $businessTrip->update($data);
    }

    public function delete(string $id): bool
    {
        $businessTrip = $this->findById($id);
        return $businessTrip->delete();
    }

    public function approve(string $id, string $userId): bool
    {
        return $this->update($id, [
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject(string $id, string $userId, string $reason): bool
    {
        return $this->update($id, [
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function checkOverlap(string $workerId, string $startDate, string $endDate, ?string $excludeId = null): bool
    {
        $query = $this->model
            ->where('worker_id', $workerId)
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
