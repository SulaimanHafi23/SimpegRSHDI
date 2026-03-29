<?php

namespace App\Services\Leave;

use App\DTOs\LeaveRequestDTO;
use App\Repositories\Contracts\Leave\LeaveRequestRepositoryInterface;
use App\Repositories\Contracts\Master\LeaveTypeRepositoryInterface;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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

    /**
     * Get count of leave requests by status
     */
    public function countByStatus(string $workerId, int $year, ?string $status = null): int
    {
        return $this->leaveRequestRepository->countByStatus($workerId, $year, $status);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
        $leaveType = $this->leaveTypeRepository->getById($data['leave_type_id']);

        // Always calculate on server-side to prevent client-side tampering.
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate = \Carbon\Carbon::parse($data['end_date']);
        $data['total_days'] = $startDate->diffInDays($endDate) + 1;

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

        // D3: Check for overlapping leave requests (pending or approved)
        $overlapping = \App\Models\LeaveRequest::where('worker_id', $data['worker_id'])
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                  ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                  ->orWhere(function ($q2) use ($data) {
                      $q2->where('start_date', '<=', $data['start_date'])
                         ->where('end_date', '>=', $data['end_date']);
                  });
            })
            ->exists();

        if ($overlapping) {
            throw new \Exception('Sudah ada permohonan cuti yang tumpang tindih pada tanggal tersebut.');
        }

        // Handle attachment
        if (isset($data['attachment']) && $leaveType->requires_attachment) {
            $data['attachment_path'] = $this->saveAttachment($data['attachment'], $data['worker_id']);
        }

        // Set default status to pending if not provided
        if (!isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'pending';
        }

        $dto = LeaveRequestDTO::fromRequest($data);
        return $this->leaveRequestRepository->create($dto);
        });
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

        // Remove empty values to prevent overwriting with empty strings
        $data = array_filter($data, function($value) {
            return $value !== '' && $value !== null && $value !== [];
        });

        // Recalculate when dates are updated to keep value consistent.
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = \Carbon\Carbon::parse($data['end_date']);
            $data['total_days'] = $startDate->diffInDays($endDate) + 1;
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

        // Send notification - get user_id from worker's user relationship
        $user = \App\Models\User::where('worker_id', $leaveRequest->worker_id)->first();
        if ($user) {
            $this->notificationService->notifyLeaveApproved(
                $user->id,
                [
                    'id' => $leaveRequest->id,
                    'start_date' => $leaveRequest->start_date,
                    'end_date' => $leaveRequest->end_date,
                ]
            );
        }

        return $result;
    }

    public function reject(string $id, string $approvedBy, string $reason)
    {
        $leaveRequest = $this->leaveRequestRepository->getById($id);

        if ($leaveRequest->status !== 'pending') {
            throw new \Exception('Hanya permohonan cuti yang berstatus pending yang dapat ditolak.');
        }

        $result = $this->leaveRequestRepository->reject($id, $approvedBy, $reason);

        // Send notification - get user_id from worker's user relationship
        $user = \App\Models\User::where('worker_id', $leaveRequest->worker_id)->first();
        if ($user) {
            $this->notificationService->notifyLeaveRejected(
                $user->id,
                [
                    'id' => $leaveRequest->id,
                    'start_date' => $leaveRequest->start_date,
                    'end_date' => $leaveRequest->end_date,
                ],
                $reason
            );
        }

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
