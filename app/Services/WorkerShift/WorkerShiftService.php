<?php

namespace App\Services\WorkerShift;

use App\DTOs\WorkerShiftDTO;
use App\Models\WorkerShift;
use App\Models\WorkerShiftHistory;
use App\Repositories\Contracts\WorkerShift\WorkerShiftRepositoryInterface;
use Illuminate\Support\Facades\DB;

class WorkerShiftService
{
    public function __construct(
        protected WorkerShiftRepositoryInterface $workerShiftRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->workerShiftRepository->getAll($filters);
    }

    public function getById(string $id)
    {
        return $this->workerShiftRepository->getById($id);
    }

    public function getByWorkerId(string $workerId)
    {
        return $this->workerShiftRepository->getByWorkerId($workerId);
    }

    public function getActiveByWorkerId(string $workerId)
    {
        return $this->workerShiftRepository->getActiveByWorkerId($workerId);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $skipDeactivate = (bool) ($data['skip_deactivate'] ?? false);

            // Log old shifts to history before deleting them
            if (($data['is_active'] ?? true) && !$skipDeactivate) {
                $this->logShiftsToHistory($data['worker_id'], null, 'shift_replaced');
                $this->workerShiftRepository->deleteOldShifts($data['worker_id']);
            }

            // Remove empty values to prevent overwriting with empty strings
            $data = array_filter($data, function($value) {
                return $value !== '' && $value !== null && $value !== [];
            });

            unset($data['skip_deactivate']);

            $dto = WorkerShiftDTO::fromRequest($data);
            $workerShift = $this->workerShiftRepository->create($dto);

            DB::commit();
            return $workerShift;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $workerShift = $this->workerShiftRepository->getById($id);

            // Log all OTHER shifts to history before deleting them
            $this->logShiftsToHistory($workerShift->worker_id, $id, 'shift_replaced');

            // Delete all OTHER shifts for this worker (keep only the one being edited)
            $this->workerShiftRepository->deleteOldShifts($workerShift->worker_id, $id);

            // Remove empty values but keep boolean false (e.g., is_active = false)
            $data = array_filter($data, function($value) {
                return $value !== '' && $value !== null && $value !== [];
            });

            $dto = WorkerShiftDTO::fromRequest($data);
            $updated = $this->workerShiftRepository->update($id, $dto);

            DB::commit();
            return $updated;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        // Log this shift to history before deleting
        $workerShift = $this->workerShiftRepository->getById($id);
        if ($workerShift) {
            WorkerShiftHistory::logChange(
                $workerShift->worker_id,
                $workerShift->shift_id,
                $workerShift->effective_from,
                $workerShift->effective_until,
                'shift_deleted'
            );
        }

        return $this->workerShiftRepository->delete($id);
    }

    public function deactivateOldShifts(string $workerId): void
    {
        $this->workerShiftRepository->deactivateOldShifts($workerId);
    }

    public function deleteOldShifts(string $workerId, ?string $excludeId = null): int
    {
        return $this->workerShiftRepository->deleteOldShifts($workerId, $excludeId);
    }

    public function assignShiftToWorker(string $workerId, string $shiftId, array $options = [])
    {
        return $this->create([
            'worker_id' => $workerId,
            'shift_id' => $shiftId,
            'effective_from' => $options['effective_from'] ?? now()->format('Y-m-d'),
            'effective_until' => $options['effective_until'] ?? null,
            'is_active' => true,
            'notes' => $options['notes'] ?? null,
        ]);
    }

    /**
     * Log existing shifts to history before they are deleted.
     * Optionally exclude one shift (the one being edited).
     */
    private function logShiftsToHistory(string $workerId, ?string $excludeId = null, string $reason = 'shift_replaced'): void
    {
        $query = WorkerShift::where('worker_id', $workerId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingShifts = $query->get();

        foreach ($existingShifts as $shift) {
            WorkerShiftHistory::logChange(
                $shift->worker_id,
                $shift->shift_id,
                $shift->effective_from,
                $shift->effective_until,
                $reason
            );
        }
    }

    /**
     * Get shift histories for a worker.
     */
    public function getShiftHistories(string $workerId)
    {
        return WorkerShiftHistory::where('worker_id', $workerId)
            ->with('shift', 'changedByUser')
            ->orderByDesc('changed_at')
            ->orderByDesc('created_at')
            ->get();
    }
}
