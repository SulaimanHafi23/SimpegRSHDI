<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class AttendanceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $reminderType; // 'check_in', 'check_out'
    protected ?string $shiftName;
    protected ?Carbon $shiftTime;
    protected string $workerName;

    /**
     * Create a new notification instance.
     *
     * @param string $reminderType 'check_in' or 'check_out'
     * @param string|null $shiftName Name of the shift
     * @param Carbon|null $shiftTime Time of the shift
     * @param string $workerName Name of the worker
     */
    public function __construct(string $reminderType, ?string $shiftName = null, ?Carbon $shiftTime = null, string $workerName = '')
    {
        $this->reminderType = $reminderType;
        $this->shiftName = $shiftName;
        $this->shiftTime = $shiftTime;
        $this->workerName = $workerName;
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

        // Add action button to check-in
        $url = route('employee.attendance.index');
        $buttonText = $this->reminderType === 'check_in' ? 'Absen Masuk' : 'Absen Keluar';
        $mailMessage->action($buttonText, $url);

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
            'reminder_type' => $this->reminderType,
            'message' => $this->getMessage(),
            'title' => $this->getTitle(),
            'shift_name' => $this->shiftName,
            'shift_time' => $this->shiftTime?->format('H:i'),
            'worker_name' => $this->workerName,
            'url' => route('employee.attendance.index'),
        ];
    }

    protected function getTitle(): string
    {
        return match ($this->reminderType) {
            'check_in' => 'Pengingat Absen Masuk',
            'check_out' => 'Pengingat Absen Keluar',
            default => 'Pengingat Absensi',
        };
    }

    protected function getSubject(): string
    {
        return match ($this->reminderType) {
            'check_in' => 'Pengingat Absen Masuk - ' . now()->format('d/m/Y'),
            'check_out' => 'Pengingat Absen Keluar - ' . now()->format('d/m/Y'),
            default => 'Pengingat Absensi',
        };
    }

    protected function getMessage(): string
    {
        $shiftInfo = '';
        if ($this->shiftName) {
            $shiftInfo = " ({$this->shiftName})";
        }
        if ($this->shiftTime) {
            $shiftInfo .= ' pada pukul ' . $this->shiftTime->format('H:i');
        }

        return match ($this->reminderType) {
            'check_in' => "Ini adalah pengingat bahwa Anda harus melakukan absen masuk hari ini{$shiftInfo}.",
            'check_out' => "Ini adalah pengingat bahwa Anda harus melakukan absen keluar hari ini{$shiftInfo}.",
            default => "Ini adalah pengingat absensi untuk Anda.",
        };
    }
}
