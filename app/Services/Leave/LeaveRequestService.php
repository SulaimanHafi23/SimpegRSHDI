<?php

namespace App\Services\Leave;

use App\DTOs\LeaveRequestDTO;
use App\Repositories\Contracts\Leave\LeaveRequestRepositoryInterface;
use App\Repositories\Contracts\Master\LeaveTypeRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class LeaveRequestService
{
    public function __construct(
        protected LeaveRequestRepositoryInterface $leaveRequestRepository,
        protected LeaveTypeRepositoryInterface $leaveTypeRepository,
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->leaveRequestRepository->getAll($filters);
    }

    public function getById(string $id)
    {
        return $this->leaveRequestRepository->getById($id);
    }

    public function getByWorkerId(string $workerId, array $filters = [])
    {
        return $this->leaveRequestRepository->getByWorkerId($workerId, $filters);
    }

    public function getPendingRequests()
    {
        return $this->leaveRequestRepository->getPendingRequests();
    }

    public function create(array $data)
    {
        $leaveType = $this->leaveTypeRepository->getById($data['leave_type_id']);

        // Validate leave balance
        if ($leaveType->max_days_per_year) {
            $balance = $this->leaveRequestRepository->getWorkerLeaveBalance(
                $data['worker_id'],
                $data['leave_type_id'],
                now()->year
            );

            if ($balance < $data['total_days']) {
                throw new \Exception("Insufficient leave balance. Available: {$balance} days");
            }
        }

        // Validate days notice
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $daysUntilStart = now()->diffInDays($startDate, false);

        if ($daysUntilStart < $leaveType->days_notice) {
            throw new \Exception("Leave request must be submitted at least {$leaveType->days_notice} days in advance.");
        }

        // Handle attachment
        if (isset($data['attachment']) && $leaveType->requires_attachment) {
            $data['attachment_path'] = $this->saveAttachment($data['attachment'], $data['worker_id']);
        }

        $dto = LeaveRequestDTO::fromRequest($data);
        return $this->leaveRequestRepository->create($dto);
    }

    public function update(string $id, array $data)
    {
        $leaveRequest = $this->leaveRequestRepository->getById($id);

        if ($leaveRequest->status !== 'pending') {
            throw new \Exception('Only pending leave requests can be updated.');
        }

        // Handle attachment
        if (isset($data['attachment'])) {
            // Delete old attachment
            if ($leaveRequest->attachment_path && Storage::exists($leaveRequest->attachment_path)) {
                Storage::delete($leaveRequest->attachment_path);
            }
            $data['attachment_path'] = $this->saveAttachment($data['attachment'], $leaveRequest->worker_id);
        }

        $dto = LeaveRequestDTO::fromRequest($data);
        return $this->leaveRequestRepository->update($id, $dto);
    }

    public function delete(string $id): bool
    {
        return $this->leaveRequestRepository->delete($id);
    }

    public function approve(string $id, string $approvedBy)
    {
        $leaveRequest = $this->leaveRequestRepository->getById($id);

        if ($leaveRequest->status !== 'pending') {
            throw new \Exception('Only pending leave requests can be approved.');
        }

        return $this->leaveRequestRepository->approve($id, $approvedBy);
    }

    public function reject(string $id, string $approvedBy, string $reason)
    {
        $leaveRequest = $this->leaveRequestRepository->getById($id);

        if ($leaveRequest->status !== 'pending') {
            throw new \Exception('Only pending leave requests can be rejected.');
        }

        return $this->leaveRequestRepository->reject($id, $approvedBy, $reason);
    }

    public function cancel(string $id)
    {
        $leaveRequest = $this->leaveRequestRepository->getById($id);

        if (!in_array($leaveRequest->status, ['pending', 'approved'])) {
            throw new \Exception('Only pending or approved leave requests can be cancelled.');
        }

        return $this->leaveRequestRepository->cancel($id);
    }

    public function getLeaveBalance(string $workerId, string $leaveTypeId, int $year)
    {
        return $this->leaveRequestRepository->getWorkerLeaveBalance($workerId, $leaveTypeId, $year);
    }

    protected function saveAttachment($attachment, string $workerId): string
    {
        $filename = sprintf(
            '%s_leave_%s.%s',
            $workerId,
            now()->format('YmdHis'),
            $attachment->getClientOriginalExtension()
        );

        return $attachment->storeAs('leave-attachments', $filename, 'public');
    }
}