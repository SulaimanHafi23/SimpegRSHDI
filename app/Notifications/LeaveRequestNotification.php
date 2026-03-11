<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected LeaveRequest $leaveRequest;
    protected string $action;
    protected ?string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveRequest $leaveRequest, string $action, ?string $reason = null)
    {
        $this->leaveRequest = $leaveRequest;
        $this->action = $action;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $leave = $this->leaveRequest;
        $subject = $this->getSubject();
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->line($this->getMessage());

        // Add action button to view details
        $url = route('employee.leaves.index');
        $mailMessage->action('Lihat Detail', $url);

        $mailMessage->line('Terima kasih menggunakan sistem SIMPEG RSUD Haji Darlan Ismail.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'leave_id' => $this->leaveRequest->id,
            'action' => $this->action,
            'message' => $this->getMessage(),
            'title' => $this->getTitle(),
            'worker_name' => $this->leaveRequest->worker->name,
            'leave_type' => $this->leaveRequest->leaveType?->name ?? 'N/A',
            'start_date' => $this->leaveRequest->start_date->format('d/m/Y'),
            'end_date' => $this->leaveRequest->end_date->format('d/m/Y'),
            'total_days' => $this->leaveRequest->total_days,
            'status' => $this->leaveRequest->status,
            'url' => route('employee.leaves.index'),
        ];
    }

    protected function getTitle(): string
    {
        return match ($this->action) {
            'submitted' => 'Permohonan Cuti Baru',
            'approved' => 'Permohonan Cuti Disetujui',
            'rejected' => 'Permohonan Cuti Ditolak',
            'cancelled' => 'Permohonan Cuti Dibatalkan',
            default => 'Notifikasi Cuti',
        };
    }

    protected function getSubject(): string
    {
        $leaveType = $this->leaveRequest->leaveType?->name ?? 'Cuti';

        return match ($this->action) {
            'submitted' => "Permohonan {$leaveType} Baru",
            'approved' => "Permohonan {$leaveType} Disetujui",
            'rejected' => "Permohonan {$leaveType} Ditolak",
            'cancelled' => "Permohonan {$leaveType} Dibatalkan",
            default => "Notifikasi {$leaveType}",
        };
    }

    protected function getMessage(): string
    {
        $leaveType = $this->leaveRequest->leaveType?->name ?? 'cuti';
        $startDate = $this->leaveRequest->start_date->format('d/m/Y');
        $endDate = $this->leaveRequest->end_date->format('d/m/Y');
        $totalDays = $this->leaveRequest->total_days;

        $message = match ($this->action) {
            'submitted' => "Permohonan {$leaveType} Anda dari {$startDate} sampai {$endDate} ({$totalDays} hari) telah diterima dan menunggu persetujuan.",
            'approved' => "Selamat! Permohonan {$leaveType} Anda dari {$startDate} sampai {$endDate} ({$totalDays} hari) telah disetujui.",
            'rejected' => "Permohonan {$leaveType} Anda dari {$startDate} sampai {$endDate} ({$totalDays} hari) telah ditolak.",
            'cancelled' => "Permohonan {$leaveType} Anda dari {$startDate} sampai {$endDate} ({$totalDays} hari) telah dibatalkan.",
            default => "Notification tentang permohonan {$leaveType} Anda.",
        };

        if ($this->reason) {
            $message .= "\n\nAlasan: {$this->reason}";
        }

        return $message;
    }
}
