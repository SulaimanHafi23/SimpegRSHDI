<?php

namespace App\Services\ShiftOverride;

use App\DTOs\ShiftOverrideDTO;
use App\Repositories\Contracts\ShiftOverride\ShiftOverrideRepositoryInterface;

class ShiftOverrideService
{
    public function __construct(
        protected ShiftOverrideRepositoryInterface $shiftOverrideRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->shiftOverrideRepository->getAll($filters);
    }

    public function getById(string $id)
    {
        return $this->shiftOverrideRepository->getById($id);
    }

    public function getByWorkerId(string $workerId)
    {
        return $this->shiftOverrideRepository->getByWorkerId($workerId);
    }

    public function getByDate(string $date)
    {
        return $this->shiftOverrideRepository->getByDate($date);
    }

    public function create(array $data)
    {
        // Check if override already exists
        $existing = $this->shiftOverrideRepository->getByWorkerAndDate(
            $data['worker_id'],
            $data['override_date']
        );

        if ($existing) {
            throw new \Exception('Shift override already exists for this date.');
        }

        $dto = ShiftOverrideDTO::fromRequest($data);
        return $this->shiftOverrideRepository->create($dto);
    }

    public function update(string $id, array $data)
    {
        $dto = ShiftOverrideDTO::fromRequest($data);
        return $this->shiftOverrideRepository->update($id, $dto);
    }

    public function delete(string $id): bool
    {
        return $this->shiftOverrideRepository->delete($id);
    }

    public function bulkCreate(string $workerId, string $shiftId, array $dates, string $createdBy, ?string $reason = null)
    {
        $results = [];
        
        foreach ($dates as $date) {
            try {
                $results[] = $this->create([
                    'worker_id' => $workerId,
                    'shift_id' => $shiftId,
                    'override_date' => $date,
                    'reason' => $reason,
                    'created_by' => $createdBy,
                ]);
            } catch (\Exception $e) {
                // Continue with other dates if one fails
                continue;
            }
        }

        return $results;
    }
}
