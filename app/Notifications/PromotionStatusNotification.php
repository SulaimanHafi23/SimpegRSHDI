<?php

namespace App\Notifications;

use App\Models\PromotionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromotionStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly PromotionRequest $promotionRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isApproved = $this->promotionRequest->status === 'approved';
        $subject    = $isApproved
            ? 'Pengajuan Kenaikan Pangkat Disetujui'
            : 'Pengajuan Kenaikan Pangkat Ditolak';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Halo, {$notifiable->name}!");

        if ($isApproved) {
            $mail->line('Pengajuan kenaikan pangkat Anda telah **disetujui**.')
                 ->line('Golongan baru: ' . $this->promotionRequest->proposed_rank)
                 ->line('Berlaku mulai: ' . optional($this->promotionRequest->effective_date)->format('d/m/Y'))
                 ->action('Lihat Detail', route('employee.promotions.show', $this->promotionRequest->id));
        } else {
            $mail->line('Pengajuan kenaikan pangkat Anda **ditolak**.')
                 ->line('Alasan: ' . ($this->promotionRequest->rejection_reason ?? '-'))
                 ->action('Lihat Detail', route('employee.promotions.show', $this->promotionRequest->id));
        }

        return $mail->line('Terima kasih.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'                 => 'promotion_status',
            'promotion_request_id' => $this->promotionRequest->id,
            'status'               => $this->promotionRequest->status,
            'proposed_rank'        => $this->promotionRequest->proposed_rank,
            'effective_date'       => $this->promotionRequest->effective_date?->toDateString(),
            'message'              => $this->promotionRequest->status === 'approved'
                ? 'Pengajuan kenaikan pangkat Anda telah disetujui.'
                : 'Pengajuan kenaikan pangkat Anda ditolak. Alasan: ' . ($this->promotionRequest->rejection_reason ?? '-'),
        ];
    }
}
