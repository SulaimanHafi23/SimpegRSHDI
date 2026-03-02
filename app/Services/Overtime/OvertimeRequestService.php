<?php

namespace App\Services\Overtime;

use App\DTOs\OvertimeRequestDTO;
use App\Repositories\Contracts\Overtime\OvertimeRequestRepositoryInterface;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;

class OvertimeRequestService
{
    public function __construct(
        protected OvertimeRequestRepositoryInterface $overtimeRequestRepository,
        protected NotificationService $notificationService
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
        return DB::transaction(function () use ($data) {
        // Calculate total hours — handle overnight (e.g. 22:00 → 02:00 next day)
        $date = $data['overtime_date'] ?? $data['date'] ?? now()->format('Y-m-d');
        $startTime = \Carbon\Carbon::parse($date . ' ' . $data['start_time']);
        $endTime   = \Carbon\Carbon::parse($date . ' ' . $data['end_time']);
        if ($endTime->lessThanOrEqualTo($startTime)) {
            $endTime->addDay();  // overnight lembur
        }
        $data['total_hours'] = round($startTime->diffInMinutes($endTime) / 60, 2);

        // Set default status to pending if not provided
        if (!isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'pending';
        }

        $dto = OvertimeRequestDTO::fromRequest($data);
        return $this->overtimeRequestRepository->create($dto);
        });
    }

    public function update(string $id, array $data)
    {
        $overtimeRequest = $this->overtimeRequestRepository->getById($id);

        if ($overtimeRequest->status !== 'pending') {
            throw new \Exception('Hanya permohonan lembur yang berstatus pending yang dapat diubah.');
        }

        // Recalculate total hours if times changed — handle overnight
        if (isset($data['start_time']) || isset($data['end_time'])) {
            $date = $data['overtime_date'] ?? $data['date'] ?? ($overtimeRequest->overtime_date ? $overtimeRequest->overtime_date->format('Y-m-d') : now()->format('Y-m-d'));
            $startTime = \Carbon\Carbon::parse($date . ' ' . ($data['start_time'] ?? \Carbon\Carbon::parse($overtimeRequest->start_time)->format('H:i')));
            $endTime   = \Carbon\Carbon::parse($date . ' ' . ($data['end_time']   ?? \Carbon\Carbon::parse($overtimeRequest->end_time)->format('H:i')));
            if ($endTime->lessThanOrEqualTo($startTime)) {
                $endTime->addDay();  // overnight lembur
            }
            $data['total_hours'] = round($startTime->diffInMinutes($endTime) / 60, 2);
        }

        // Remove empty values to prevent overwriting with empty strings
        $data = array_filter($data, function($value) {
            return $value !== '' && $value !== null && $value !== [];
        });

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
            throw new \Exception('Hanya permohonan lembur yang berstatus pending yang dapat disetujui.');
        }

        $result = $this->overtimeRequestRepository->approve($id, $approvedBy);

        // Send notification - get user_id from worker's user relationship
        $user = \App\Models\User::where('worker_id', $overtimeRequest->worker_id)->first();
        if ($user) {
            $this->notificationService->notifyOvertimeApproved(
                $user->id,
                [
                    'id' => $overtimeRequest->id,
                    'overtime_date' => $overtimeRequest->overtime_date,
                ]
            );
        }

        return $result;
    }

    public function reject(string $id, string $approvedBy, string $reason)
    {
        $overtimeRequest = $this->overtimeRequestRepository->getById($id);

        if ($overtimeRequest->status !== 'pending') {
            throw new \Exception('Hanya permohonan lembur yang berstatus pending yang dapat ditolak.');
        }

        $result = $this->overtimeRequestRepository->reject($id, $approvedBy, $reason);

        // Send notification - get user_id from worker's user relationship
        $user = \App\Models\User::where('worker_id', $overtimeRequest->worker_id)->first();
        if ($user) {
            $this->notificationService->notifyOvertimeRejected(
                $user->id,
                [
                    'id' => $overtimeRequest->id,
                    'overtime_date' => $overtimeRequest->overtime_date,
                ],
                $reason
            );
        }

        return $result;
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
