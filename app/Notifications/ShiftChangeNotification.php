<?php

namespace App\Notifications;

use App\Models\ShiftOverride;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ShiftOverride $shiftOverride;
    protected string $action; // 'created', 'approved', 'rejected', 'executed', 'cancelled'
    protected ?string $reason;

    /**
     * Create a new notification instance.
     *
     * @param ShiftOverride $shiftOverride The shift override record
     * @param string $action The action taken on the shift override
     * @param string|null $reason Optional reason for action
     */
    public function __construct(ShiftOverride $shiftOverride, string $action, ?string $reason = null)
    {
        $this->shiftOverride = $shiftOverride;
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
        $subject = $this->getSubject();
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->line($this->getMessage());

        // Add shift information
        $mailMessage->line('Detail Perubahan Shift:')
            ->line('- **Tanggal**: ' . $this->shiftOverride->override_date->format('d/m/Y'))
            ->line('- **Shift**: ' . ($this->shiftOverride->shift?->name ?? 'N/A'));

        // Add action button
        $url = route('employee.shifts.index');
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
            'shift_override_id' => $this->shiftOverride->id,
            'action' => $this->action,
            'message' => $this->getMessage(),
            'title' => $this->getTitle(),
            'worker_name' => $this->shiftOverride->worker->name,
            'date' => $this->shiftOverride->override_date->format('d/m/Y'),
            'shift' => $this->shiftOverride->shift?->name,
            'reason' => $this->shiftOverride->reason,
            'url' => route('employee.shifts.index'),
        ];
    }

    protected function getTitle(): string
    {
        return match ($this->action) {
            'created' => 'Perubahan Shift Baru',
            'approved' => 'Perubahan Shift Disetujui',
            'rejected' => 'Perubahan Shift Ditolak',
            'executed' => 'Perubahan Shift Dijalankan',
            'cancelled' => 'Perubahan Shift Dibatalkan',
            default => 'Notifikasi Perubahan Shift',
        };
    }

    protected function getSubject(): string
    {
        return match ($this->action) {
            'created' => 'Perubahan Shift Baru - ' . $this->shiftOverride->override_date->format('d/m/Y'),
            'approved' => 'Perubahan Shift Disetujui - ' . $this->shiftOverride->override_date->format('d/m/Y'),
            'rejected' => 'Perubahan Shift Ditolak - ' . $this->shiftOverride->override_date->format('d/m/Y'),
            'executed' => 'Perubahan Shift Dijalankan - ' . $this->shiftOverride->override_date->format('d/m/Y'),
            'cancelled' => 'Perubahan Shift Dibatalkan - ' . $this->shiftOverride->override_date->format('d/m/Y'),
            default => 'Notifikasi Perubahan Shift',
        };
    }

    protected function getMessage(): string
    {
        $date = $this->shiftOverride->override_date->format('d/m/Y');
        $shift = $this->shiftOverride->shift?->name ?? 'N/A';

        $message = match ($this->action) {
            'created' => "Ada perubahan shift untuk Anda pada {$date}: Shift {$shift}. Menunggu persetujuan supervisor.",
            'approved' => "Perubahan shift Anda pada {$date} telah disetujui: Shift {$shift}.",
            'rejected' => "Perubahan shift Anda pada {$date} telah ditolak: Shift {$shift}.",
            'executed' => "Perubahan shift Anda pada {$date} telah dilaksanakan: Shift {$shift}.",
            'cancelled' => "Perubahan shift Anda pada {$date} telah dibatalkan.",
            default => "Notifikasi tentang perubahan shift Anda.",
        };

        if ($this->reason) {
            $message .= "\n\nAlasan/Catatan: {$this->reason}";
        }

        return $message;
    }
}
