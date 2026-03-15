<?php

namespace App\Notifications;

use App\Models\WorkerDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkerDocumentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected WorkerDocument $document;
    protected string $action;
    protected ?string $reason;

    public function __construct(WorkerDocument $document, string $action, ?string $reason = null)
    {
        $this->document = $document;
        $this->action = $action;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $documentName = $this->document->documentType?->name
            ?? $this->document->departmentDocumentType?->customDocumentType?->name
            ?? 'Dokumen';

        $workerName = $this->document->worker?->name ?? $notifiable->name;

        $mailMessage = (new MailMessage)
            ->subject($this->getSubject($documentName))
            ->greeting('Yth. ' . $workerName . ',')
            ->line($this->getMessage($documentName));

        if ($this->action === 'rejected' && $this->reason) {
            $mailMessage->line('**Alasan penolakan:** ' . $this->reason);
            $mailMessage->line('Mohon upload ulang dokumen yang sesuai dengan ketentuan.');
        }

        if ($this->action === 'verified') {
            $mailMessage->line('Dokumen Anda telah diverifikasi dan sudah aktif dalam sistem.');
        }

        $mailMessage->action('Lihat Dokumen Saya', url('/employee/documents'));
        $mailMessage->line('Terima kasih menggunakan sistem SIDIA - Sistem Informasi Darlan Ismail dan Absensi.');

        return $mailMessage;
    }

    public function toArray(object $notifiable): array
    {
        $documentName = $this->document->documentType?->name
            ?? $this->document->departmentDocumentType?->customDocumentType?->name
            ?? 'Dokumen';

        return [
            'document_id' => $this->document->id,
            'document_name' => $documentName,
            'action' => $this->action,
            'reason' => $this->reason,
            'message' => $this->getMessage($documentName),
        ];
    }

    private function getSubject(string $documentName): string
    {
        return match ($this->action) {
            'verified' => "[SIDIA] Dokumen {$documentName} Anda Telah Diverifikasi",
            'rejected' => "[SIDIA] Dokumen {$documentName} Anda Ditolak",
            default    => "[SIDIA] Update Status Dokumen {$documentName}",
        };
    }

    private function getMessage(string $documentName): string
    {
        return match ($this->action) {
            'verified' => "Dokumen {$documentName} yang Anda upload telah diverifikasi dan disetujui oleh admin.",
            'rejected' => "Dokumen {$documentName} yang Anda upload ditolak oleh admin.",
            default    => "Terdapat pembaruan status pada dokumen {$documentName} Anda.",
        };
    }
}
