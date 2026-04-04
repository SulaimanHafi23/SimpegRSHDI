<?php

namespace App\Services\Notification;

use App\DTOs\NotificationDTO;
use App\Repositories\Contracts\Notification\NotificationRepositoryInterface;

class NotificationService
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->notificationRepository->getAll($filters);
    }

    public function getById(string $id)
    {
        return $this->notificationRepository->getById($id);
    }

    public function getByUserId(string $userId, array $filters = [])
    {
        return $this->notificationRepository->getByUserId($userId, $filters);
    }

    public function getUnreadByUserId(string $userId)
    {
        return $this->notificationRepository->getUnreadByUserId($userId);
    }

    public function getUnreadCount(string $userId): int
    {
        return $this->notificationRepository->getUnreadCount($userId);
    }

    public function create(array $data)
    {
        $dto = NotificationDTO::fromRequest($data);
        return $this->notificationRepository->create($dto);
    }

    public function markAsRead(string $id): bool
    {
        return $this->notificationRepository->markAsRead($id);
    }

    public function markAllAsRead(string $userId): bool
    {
        return $this->notificationRepository->markAllAsRead($userId);
    }

    public function delete(string $id): bool
    {
        return $this->notificationRepository->delete($id);
    }

    /**
     * Send notification when leave is approved
     */
    public function notifyLeaveApproved(string $userId, array $leaveData): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => 'leave_approved',
            'title' => 'Cuti Disetujui',
            'message' => sprintf(
                'Permohonan cuti Anda dari %s sampai %s telah disetujui.',
                $leaveData['start_date'],
                $leaveData['end_date']
            ),
            'data' => [
                'leave_id' => $leaveData['id'],
                'type' => 'leave',
                'action' => 'approved'
            ],
        ]);
    }

    /**
     * Send notification when leave is rejected
     */
    public function notifyLeaveRejected(string $userId, array $leaveData, ?string $reason = null): void
    {
        $message = sprintf(
            'Permohonan cuti Anda dari %s sampai %s telah ditolak.',
            $leaveData['start_date'],
            $leaveData['end_date']
        );

        if ($reason) {
            $message .= ' Alasan: ' . $reason;
        }

        $this->create([
            'user_id' => $userId,
            'type' => 'leave_rejected',
            'title' => 'Cuti Ditolak',
            'message' => $message,
            'data' => [
                'leave_id' => $leaveData['id'],
                'type' => 'leave',
                'action' => 'rejected',
                'reason' => $reason
            ],
        ]);
    }

    /**
     * Send notification when document is verified
     */
    public function notifyDocumentVerified(string $userId, array $documentData): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => 'document_verified',
            'title' => 'Dokumen Terverifikasi',
            'message' => sprintf(
                'Dokumen %s Anda telah diverifikasi.',
                $documentData['document_type']
            ),
            'data' => [
                'document_id' => $documentData['id'],
                'type' => 'document',
                'action' => 'verified'
            ],
        ]);
    }

    /**
     * Send notification when document is rejected
     */
    public function notifyDocumentRejected(string $userId, array $documentData, ?string $reason = null): void
    {
        $message = sprintf(
            'Dokumen %s Anda ditolak.',
            $documentData['document_type']
        );

        if ($reason) {
            $message .= ' Alasan: ' . $reason;
        }

        $this->create([
            'user_id' => $userId,
            'type' => 'document_rejected',
            'title' => 'Dokumen Ditolak',
            'message' => $message,
            'data' => [
                'document_id' => $documentData['id'],
                'type' => 'document',
                'action' => 'rejected',
                'reason' => $reason
            ],
        ]);
    }

    /**
     * Send notification when business trip is approved
     */
    public function notifyBusinessTripApproved(string $userId, array $tripData): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => 'business_trip_approved',
            'title' => 'Perjalanan Dinas Disetujui',
            'message' => sprintf(
                'Permohonan perjalanan dinas ke %s telah disetujui.',
                $tripData['destination']
            ),
            'data' => [
                'business_trip_id' => $tripData['id'],
                'type' => 'business_trip',
                'action' => 'approved'
            ],
        ]);
    }

    /**
     * Send notification when business trip is rejected
     */
    public function notifyBusinessTripRejected(string $userId, array $tripData, ?string $reason = null): void
    {
        $message = sprintf(
            'Permohonan perjalanan dinas ke %s telah ditolak.',
            $tripData['destination']
        );

        if ($reason) {
            $message .= ' Alasan: ' . $reason;
        }

        $this->create([
            'user_id' => $userId,
            'type' => 'business_trip_rejected',
            'title' => 'Perjalanan Dinas Ditolak',
            'message' => $message,
            'data' => [
                'business_trip_id' => $tripData['id'],
                'type' => 'business_trip',
                'action' => 'rejected',
                'reason' => $reason
            ],
        ]);
    }

    /**
     * Send notification for shift swap request
     */
    public function notifyShiftSwapRequest(string $userId, array $swapData): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => 'shift_swap_request',
            'title' => 'Permintaan Tukar Shift',
            'message' => sprintf(
                '%s mengajukan permintaan tukar shift dengan Anda.',
                $swapData['requester_name']
            ),
            'data' => [
                'shift_swap_id' => $swapData['id'],
                'type' => 'shift_swap',
                'action' => 'request'
            ],
        ]);
    }

    /**
     * Send notification when shift swap is accepted
     */
    public function notifyShiftSwapAccepted(string $userId, array $swapData): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => 'shift_swap_accepted',
            'title' => 'Tukar Shift Diterima',
            'message' => sprintf(
                '%s menerima permintaan tukar shift Anda.',
                $swapData['target_name']
            ),
            'data' => [
                'shift_swap_id' => $swapData['id'],
                'type' => 'shift_swap',
                'action' => 'accepted'
            ],
        ]);
    }

    /**
     * Send notification when shift swap is rejected
     */
    public function notifyShiftSwapRejected(string $userId, array $swapData): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => 'shift_swap_rejected',
            'title' => 'Tukar Shift Ditolak',
            'message' => sprintf(
                '%s menolak permintaan tukar shift Anda.',
                $swapData['target_name']
            ),
            'data' => [
                'shift_swap_id' => $swapData['id'],
                'type' => 'shift_swap',
                'action' => 'rejected'
            ],
        ]);
    }

    /**
     * Send reminder for upcoming shift
     */
    public function notifyUpcomingShift(string $userId, array $shiftData): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => 'shift_reminder',
            'title' => 'Pengingat Shift',
            'message' => sprintf(
                'Anda memiliki shift %s besok pukul %s.',
                $shiftData['shift_name'],
                $shiftData['start_time']
            ),
            'data' => [
                'shift_id' => $shiftData['id'],
                'type' => 'shift',
                'action' => 'reminder'
            ],
        ]);
    }
}
