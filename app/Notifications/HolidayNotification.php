<?php

namespace App\Notifications;

use App\Models\Holiday;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HolidayNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Holiday $holiday;
    protected string $type; // 'upcoming', 'reminder'

    /**
     * Create a new notification instance.
     *
     * @param Holiday $holiday The holiday that's upcoming
     * @param string $type 'upcoming' for initial notification, 'reminder' for reminders
     */
    public function __construct(Holiday $holiday, string $type = 'upcoming')
    {
        $this->holiday = $holiday;
        $this->type = $type;
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
        $subject = $this->getSubject();
        $holidayDate = \Illuminate\Support\Carbon::parse($this->holiday->date)->format('d/m/Y');
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->line($this->getMessage());

        // Add info about the holiday
        $mailMessage->line('Detail Hari Libur:')
            ->line('- **Nama**: ' . $this->holiday->name)
            ->line('- **Tanggal**: ' . $holidayDate)
            ->line('- **Keterangan**: ' . ($this->holiday->description ?? 'N/A'));

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
        $holidayDate = \Illuminate\Support\Carbon::parse($this->holiday->date)->format('d/m/Y');

        return [
            'holiday_id' => $this->holiday->id,
            'type' => $this->type,
            'message' => $this->getMessage(),
            'title' => $this->getTitle(),
            'holiday_name' => $this->holiday->name,
            'holiday_date' => $holidayDate,
            'description' => $this->holiday->description,
            'url' => null,
        ];
    }

    protected function getTitle(): string
    {
        return match ($this->type) {
            'upcoming' => 'Hari Libur Mendatang: ' . $this->holiday->name,
            'reminder' => 'Pengingat Hari Libur: ' . $this->holiday->name,
            default => 'Notifikasi Hari Libur',
        };
    }

    protected function getSubject(): string
    {
        return match ($this->type) {
            'upcoming' => 'Hari Libur Mendatang - ' . $this->holiday->name,
            'reminder' => 'Pengingat Hari Libur - ' . $this->holiday->name,
            default => 'Notifikasi Hari Libur',
        };
    }

    protected function getMessage(): string
    {
        $date = \Illuminate\Support\Carbon::parse($this->holiday->date)->format('d/m/Y');
        $daysUntil = now()->diffInDays($this->holiday->date, false);

        return match ($this->type) {
            'upcoming' => "Ada hari libur mendatang bernama '{$this->holiday->name}' pada {$date} ({$daysUntil} hari lagi).",
            'reminder' => "Ini adalah pengingat tentang hari libur '{$this->holiday->name}' yang jatuh pada {$date} (besok).",
            default => "Notifikasi tentang hari libur '{$this->holiday->name}'.",
        };
    }
}
