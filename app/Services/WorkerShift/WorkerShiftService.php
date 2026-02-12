<?php

namespace App\Services\WorkerShift;

use App\DTOs\WorkerShiftDTO;
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

            // Deactivate old shifts if setting new active shift
            if (($data['is_active'] ?? true) && !$skipDeactivate) {
                $this->workerShiftRepository->deactivateOldShifts($data['worker_id']);
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
            // Deactivate old shifts if activating this shift
            if ($data['is_active'] ?? false) {
                $workerShift = $this->workerShiftRepository->getById($id);
                $this->workerShiftRepository->deactivateOldShifts($workerShift->worker_id);
            }

            // Remove empty values to prevent overwriting with empty strings
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
        return $this->workerShiftRepository->delete($id);
    }

    public function deactivateOldShifts(string $workerId): void
    {
        $this->workerShiftRepository->deactivateOldShifts($workerId);
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
}
