<?php

namespace App\Notifications;

use App\Models\OvertimeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OvertimeRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected OvertimeRequest $overtimeRequest;
    protected string $action;
    protected ?string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(OvertimeRequest $overtimeRequest, string $action, ?string $reason = null)
    {
        $this->overtimeRequest = $overtimeRequest;
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $overtime = $this->overtimeRequest;
        $subject = $this->getSubject();
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->line($this->getMessage());

        // Add action button to view details
        $url = route('employee.overtimes.index');
        $mailMessage->action('Lihat Detail', $url);

        $mailMessage->line('Terima kasih menggunakan sistem SIDIA - Sistem Informasi Darlan Ismail dan Absensi.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $overtimeDate = $this->formatOvertimeDate();

        return [
            'overtime_id' => $this->overtimeRequest->id,
            'action' => $this->action,
            'message' => $this->getMessage(),
            'title' => $this->getTitle(),
            'worker_name' => $this->overtimeRequest->worker->name,
            'overtime_date' => $overtimeDate,
            'start_time' => $this->overtimeRequest->start_time,
            'end_time' => $this->overtimeRequest->end_time,
            'status' => $this->overtimeRequest->status,
            'url' => route('employee.overtimes.index'),
        ];
    }

    protected function getTitle(): string
    {
        return match ($this->action) {
            'submitted' => 'Permohonan Lembur Baru',
            'approved' => 'Permohonan Lembur Disetujui',
            'rejected' => 'Permohonan Lembur Ditolak',
            'cancelled' => 'Permohonan Lembur Dibatalkan',
            default => 'Notifikasi Lembur',
        };
    }

    protected function getSubject(): string
    {
        $overtimeDate = $this->formatOvertimeDate();

        return match ($this->action) {
            'submitted' => 'Permohonan Lembur Baru - ' . $overtimeDate,
            'approved' => 'Permohonan Lembur Disetujui - ' . $overtimeDate,
            'rejected' => 'Permohonan Lembur Ditolak - ' . $overtimeDate,
            'cancelled' => 'Permohonan Lembur Dibatalkan - ' . $overtimeDate,
            default => 'Notifikasi Lembur',
        };
    }

    protected function getMessage(): string
    {
        $date = $this->formatOvertimeDate();
        $time = $this->overtimeRequest->start_time . ' - ' . $this->overtimeRequest->end_time;

        $message = match ($this->action) {
            'submitted' => "Permohonan lembur Anda untuk tanggal {$date} ({$time}) telah diterima dan menunggu persetujuan.",
            'approved' => "Selamat! Permohonan lembur Anda untuk tanggal {$date} ({$time}) telah disetujui.",
            'rejected' => "Permohonan lembur Anda untuk tanggal {$date} ({$time}) telah ditolak.",
            'cancelled' => "Permohonan lembur Anda untuk tanggal {$date} ({$time}) telah dibatalkan.",
            default => "Notification tentang permohonan lembur Anda.",
        };

        if ($this->reason) {
            $message .= "\n\nAlasan: {$this->reason}";
        }

        return $message;
    }

    private function formatOvertimeDate(): string
    {
        return date('d/m/Y', strtotime((string) $this->overtimeRequest->overtime_date));
    }
}
