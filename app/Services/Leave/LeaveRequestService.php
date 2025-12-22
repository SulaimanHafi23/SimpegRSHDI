<?php

namespace App\Services\Leave;

use App\DTOs\LeaveRequestDTO;
use App\Repositories\Contracts\Leave\LeaveRequestRepositoryInterface;
use App\Repositories\Contracts\Master\LeaveTypeRepositoryInterface;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Storage;

class LeaveRequestService
{
    public function __construct(
        protected LeaveRequestRepositoryInterface $leaveRequestRepository,
        protected LeaveTypeRepositoryInterface $leaveTypeRepository,
        protected NotificationService $notificationService,
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
                throw new \Exception("Sisa cuti tidak mencukupi. Sisa cuti tersedia: {$balance} hari");
            }
        }

        // Validate days notice
        $startDate = \Carbon\Carbon::parse($data['start_date'])->startOfDay();
        $today = now()->startOfDay();
        $daysUntilStart = $today->diffInDays($startDate, false);

        // Only validate if start date is in the future and days_notice is required
        if ($startDate->isFuture() && $daysUntilStart < $leaveType->days_notice) {
            throw new \Exception("Permohonan cuti harus diajukan minimal {$leaveType->days_notice} hari sebelumnya.");
        }

        // Don't allow backdated leave requests
        if ($startDate->isPast()) {
            throw new \Exception("Tidak dapat mengajukan cuti untuk tanggal yang sudah lewat.");
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
            throw new \Exception('Hanya permohonan cuti yang berstatus pending yang dapat diubah.');
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
            throw new \Exception('Hanya permohonan cuti yang berstatus pending yang dapat disetujui.');
        }

        $result = $this->leaveRequestRepository->approve($id, $approvedBy);

        // Send notification
        $this->notificationService->notifyLeaveApproved(
            $leaveRequest->worker->user_id,
            [
                'id' => $leaveRequest->id,
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
            ]
        );

        return $result;
    }

    public function reject(string $id, string $approvedBy, string $reason)
    {
        $leaveRequest = $this->leaveRequestRepository->getById($id);

        if ($leaveRequest->status !== 'pending') {
            throw new \Exception('Hanya permohonan cuti yang berstatus pending yang dapat ditolak.');
        }

        $result = $this->leaveRequestRepository->reject($id, $approvedBy, $reason);

        // Send notification
        $this->notificationService->notifyLeaveRejected(
            $leaveRequest->worker->user_id,
            [
                'id' => $leaveRequest->id,
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
            ],
            $reason
        );

        return $result;
    }

    public function cancel(string $id)
    {
        $leaveRequest = $this->leaveRequestRepository->getById($id);

        if (!in_array($leaveRequest->status, ['pending', 'approved'])) {
            throw new \Exception('Hanya permohonan cuti yang berstatus pending atau approved yang dapat dibatalkan.');
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