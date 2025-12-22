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
     * Send notification when overtime is approved
     */
    public function notifyOvertimeApproved(string $userId, array $overtimeData): void
    {
        $this->create([
            'user_id' => $userId,
            'type' => 'overtime_approved',
            'title' => 'Lembur Disetujui',
            'message' => sprintf(
                'Permohonan lembur Anda pada %s telah disetujui.',
                $overtimeData['overtime_date']
            ),
            'data' => [
                'overtime_id' => $overtimeData['id'],
                'type' => 'overtime',
                'action' => 'approved'
            ],
        ]);
    }

    /**
     * Send notification when overtime is rejected
     */
    public function notifyOvertimeRejected(string $userId, array $overtimeData, ?string $reason = null): void
    {
        $message = sprintf(
            'Permohonan lembur Anda pada %s telah ditolak.',
            $overtimeData['overtime_date']
        );
        
        if ($reason) {
            $message .= ' Alasan: ' . $reason;
        }

        $this->create([
            'user_id' => $userId,
            'type' => 'overtime_rejected',
            'title' => 'Lembur Ditolak',
            'message' => $message,
            'data' => [
                'overtime_id' => $overtimeData['id'],
                'type' => 'overtime',
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
}
