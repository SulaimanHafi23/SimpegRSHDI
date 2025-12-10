<?php

// filepath: app/Repositories/BusinessTrip/BusinessTripReportRepository.php

namespace App\Repositories\BusinessTrip;

use App\Models\BusinessTripReport;
use App\Repositories\Contracts\BusinessTrip\BusinessTripReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BusinessTripReportRepository implements BusinessTripReportRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly BusinessTripReport $model
    ) {}

    public function findById(string $id)
    {
        return $this->model
            ->with(['businessTrip.worker', 'reviewer'])
            ->findOrFail($id);
    }

    public function getByBusinessTrip(string $businessTripId): Collection
    {
        return $this->model
            ->where('business_trip_id', $businessTripId)
            ->orderBy('report_date', 'desc')
            ->get();
    }

    public function getPendingReview(): Collection
    {
        return $this->model
            ->with(['businessTrip.worker'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $report = $this->findById($id);
        return $report->update($data);
    }

    public function delete(string $id): bool
    {
        $report = $this->findById($id);
        return $report->delete();
    }

    public function approve(string $id, string $userId, ?string $notes = null): bool
    {
        return $this->update($id, [
            'status' => 'approved',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    public function reject(string $id, string $userId, string $notes): bool
    {
        return $this->update($id, [
            'status' => 'rejected',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }
}
