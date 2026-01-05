<?php

namespace App\Notifications;

use App\Models\ShiftSwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftSwapNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ShiftSwapRequest $swapRequest;
    protected string $action;
    protected ?string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(ShiftSwapRequest $swapRequest, string $action, ?string $message = null)
    {
        $this->swapRequest = $swapRequest;
        $this->action = $action;
        $this->message = $message;
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
        $swap = $this->swapRequest;
        $subject = $this->getSubject();
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->line($this->getMessage());

        // Add action button based on action type
        if (in_array($this->action, ['created', 'accepted', 'awaiting_approval'])) {
            $url = route('employee.shift-swaps.index');
            $mailMessage->action('Lihat Detail', $url);
        } elseif ($this->action === 'manager_approval_needed') {
            $url = route('manager.shift-swap-approvals.index');
            $mailMessage->action('Review Permintaan', $url);
        }

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
            'swap_id' => $this->swapRequest->id,
            'action' => $this->action,
            'message' => $this->getMessage(),
            'title' => $this->getTitle(),
            'requester_name' => $this->swapRequest->requester->name,
            'target_name' => $this->swapRequest->targetWorker?->name,
            'status' => $this->swapRequest->status,
            'url' => $this->getUrl(),
        ];
    }

    protected function getTitle(): string
    {
        return match ($this->action) {
            'created' => 'Permintaan Tukar Shift Baru',
            'accepted' => 'Permintaan Diterima',
            'rejected' => 'Permintaan Ditolak',
            'cancelled' => 'Permintaan Dibatalkan',
            'awaiting_approval' => 'Menunggu Persetujuan Manager',
            'approved_by_manager' => 'Permintaan Disetujui Manager',
            'rejected_by_manager' => 'Permintaan Ditolak Manager',
            'executed' => 'Pertukaran Shift Berhasil',
            'manager_approval_needed' => 'Persetujuan Diperlukan',
            default => 'Notifikasi Tukar Shift',
        };
    }

    protected function getSubject(): string
    {
        return '[SIMPEG] ' . $this->getTitle();
    }

    protected function getMessage(): string
    {
        if ($this->message) {
            return $this->message;
        }

        $swap = $this->swapRequest;
        $requester = $swap->requester->name;
        $target = $swap->targetWorker?->name ?? 'Terbuka';

        return match ($this->action) {
            'created' => "Permintaan tukar shift baru dari {$requester} kepada {$target}.",
            'accepted' => "{$target} telah menerima permintaan tukar shift Anda.",
            'rejected' => "{$target} menolak permintaan tukar shift Anda.",
            'cancelled' => "{$requester} membatalkan permintaan tukar shift.",
            'awaiting_approval' => "Permintaan tukar shift Anda menunggu persetujuan manager.",
            'approved_by_manager' => "Manager menyetujui permintaan tukar shift Anda.",
            'rejected_by_manager' => "Manager menolak permintaan tukar shift Anda.",
            'executed' => "Pertukaran shift antara {$requester} dan {$target} telah berhasil dieksekusi.",
            'manager_approval_needed' => "Permintaan tukar shift cross-department dari {$requester} memerlukan persetujuan Anda.",
            default => "Perubahan status pada permintaan tukar shift.",
        };
    }

    protected function getUrl(): string
    {
        if ($this->action === 'manager_approval_needed') {
            return route('manager.shift-swap-approvals.show', $this->swapRequest->id);
        }
        return route('employee.shift-swaps.index');
    }
}
