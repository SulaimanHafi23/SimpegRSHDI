<?php

namespace App\Services\Overtime;

use App\DTOs\OvertimeRequestDTO;
use App\Repositories\Contracts\Overtime\OvertimeRequestRepositoryInterface;

class OvertimeRequestService
{
    public function __construct(
        protected OvertimeRequestRepositoryInterface $overtimeRequestRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->overtimeRequestRepository->getAll($filters);
    }

    public function getById(string $id)
    {
        return $this->overtimeRequestRepository->getById($id);
    }

    public function getByWorkerId(string $workerId, array $filters = [])
    {
        return $this->overtimeRequestRepository->getByWorkerId($workerId, $filters);
    }

    public function getPendingRequests()
    {
        return $this->overtimeRequestRepository->getPendingRequests();
    }

    public function create(array $data)
    {
        // Calculate total hours
        $startTime = \Carbon\Carbon::parse($data['start_time']);
        $endTime = \Carbon\Carbon::parse($data['end_time']);
        $data['total_hours'] = $endTime->diffInHours($startTime);

        $dto = OvertimeRequestDTO::fromRequest($data);
        return $this->overtimeRequestRepository->create($dto);
    }

    public function update(string $id, array $data)
    {
        $overtimeRequest = $this->overtimeRequestRepository->getById($id);

        if ($overtimeRequest->status !== 'pending') {
            throw new \Exception('Only pending overtime requests can be updated.');
        }

        // Recalculate total hours if times changed
        if (isset($data['start_time']) || isset($data['end_time'])) {
            $startTime = \Carbon\Carbon::parse($data['start_time'] ?? $overtimeRequest->start_time);
            $endTime = \Carbon\Carbon::parse($data['end_time'] ?? $overtimeRequest->end_time);
            $data['total_hours'] = $endTime->diffInHours($startTime);
        }

        $dto = OvertimeRequestDTO::fromRequest($data);
        return $this->overtimeRequestRepository->update($id, $dto);
    }

    public function delete(string $id): bool
    {
        return $this->overtimeRequestRepository->delete($id);
    }

    public function approve(string $id, string $approvedBy)
    {
        $overtimeRequest = $this->overtimeRequestRepository->getById($id);

        if ($overtimeRequest->status !== 'pending') {
            throw new \Exception('Only pending overtime requests can be approved.');
        }

        return $this->overtimeRequestRepository->approve($id, $approvedBy);
    }

    public function reject(string $id, string $approvedBy, string $reason)
    {
        $overtimeRequest = $this->overtimeRequestRepository->getById($id);

        if ($overtimeRequest->status !== 'pending') {
            throw new \Exception('Only pending overtime requests can be rejected.');
        }

        return $this->overtimeRequestRepository->reject($id, $approvedBy, $reason);
    }

    public function bulkApprove(array $ids, string $approvedBy)
    {
        $results = [];
        
        foreach ($ids as $id) {
            try {
                $results[] = $this->approve($id, $approvedBy);
            } catch (\Exception $e) {
                continue;
            }
        }

        return $results;
    }
}