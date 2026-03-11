<?php

namespace App\Notifications;

use App\Models\WorkerDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $document;
    protected $daysUntilExpiry;
    protected $urgencyLevel;

    /**
     * Create a new notification instance.
     */
    public function __construct(WorkerDocument $document, int $daysUntilExpiry, string $urgencyLevel = 'normal')
    {
        $this->document = $document;
        $this->daysUntilExpiry = $daysUntilExpiry;
        $this->urgencyLevel = $urgencyLevel;
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
        $documentName = $this->document->documentType?->name
            ?? $this->document->departmentDocumentType?->customDocumentType?->name
            ?? 'Dokumen';

        $subject = $this->getSubject();
        $greeting = $this->getGreeting();
        $message = $this->getMessage($documentName);

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($message)
            ->line('Detail dokumen:')
            ->line('- **Nama Dokumen**: ' . $documentName)
            ->line('- **Tanggal Kadaluarsa**: ' . $this->document->expired_date->format('d/m/Y'))
            ->line('- **Nama Pegawai**: ' . $this->document->worker->name);

        if ($this->urgencyLevel === 'critical') {
            $mailMessage->line('⚠️ **PENTING**: Dokumen ini sudah kadaluarsa dan memerlukan perhatian segera.');
        } elseif ($this->urgencyLevel === 'urgent') {
            $mailMessage->line('⚠️ **PERHATIAN**: Dokumen ini akan segera kadaluarsa. Mohon segera diperbarui.');
        }

        $mailMessage->action('Lihat Detail Dokumen', route('admin.documents.show', $this->document->id));

        if ($this->daysUntilExpiry > 0) {
            $mailMessage->line('Pastikan untuk memperbarui dokumen sebelum tanggal kadaluarsa.');
        } else {
            $mailMessage->line('Segera upload dokumen yang baru untuk menggantikan dokumen yang telah kadaluarsa.');
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $documentName = $this->document->documentType?->name
            ?? $this->document->departmentDocumentType?->customDocumentType?->name
            ?? 'Dokumen';

        return [
            'type' => 'document_expiry',
            'document_id' => $this->document->id,
            'document_name' => $documentName,
            'worker_id' => $this->document->worker_id,
            'worker_name' => $this->document->worker->name,
            'expired_date' => $this->document->expired_date->format('Y-m-d'),
            'days_until_expiry' => $this->daysUntilExpiry,
            'urgency_level' => $this->urgencyLevel,
            'message' => $this->getMessage($documentName),
            'action_url' => route('admin.documents.show', $this->document->id),
        ];
    }

    /**
     * Get notification subject based on urgency.
     */
    private function getSubject(): string
    {
        if ($this->urgencyLevel === 'critical') {
            return '🔴 URGENT: Dokumen Pegawai Sudah Kadaluarsa';
        }

        if ($this->urgencyLevel === 'urgent') {
            return '⚠️ PENTING: Dokumen Pegawai Akan Kadaluarsa Dalam ' . $this->daysUntilExpiry . ' Hari';
        }

        return '📄 Pengingat: Dokumen Pegawai Akan Kadaluarsa';
    }

    /**
     * Get notification greeting based on urgency.
     */
    private function getGreeting(): string
    {
        if ($this->urgencyLevel === 'critical') {
            return 'Perhatian!';
        }

        if ($this->urgencyLevel === 'urgent') {
            return 'Pengingat Penting';
        }

        return 'Halo';
    }

    /**
     * Get notification message.
     */
    private function getMessage(string $documentName): string
    {
        if ($this->daysUntilExpiry < 0) {
            $daysExpired = abs($this->daysUntilExpiry);
            return "Dokumen **{$documentName}** milik pegawai **{$this->document->worker->name}** telah kadaluarsa sejak **{$daysExpired} hari yang lalu** (tanggal kadaluarsa: {$this->document->expired_date->format('d/m/Y')}).";
        }

        if ($this->daysUntilExpiry === 0) {
            return "Dokumen **{$documentName}** milik pegawai **{$this->document->worker->name}** akan kadaluarsa **hari ini** (tanggal kadaluarsa: {$this->document->expired_date->format('d/m/Y')}).";
        }

        if ($this->daysUntilExpiry === 1) {
            return "Dokumen **{$documentName}** milik pegawai **{$this->document->worker->name}** akan kadaluarsa **besok** (tanggal kadaluarsa: {$this->document->expired_date->format('d/m/Y')}).";
        }

        return "Dokumen **{$documentName}** milik pegawai **{$this->document->worker->name}** akan kadaluarsa dalam **{$this->daysUntilExpiry} hari** (tanggal kadaluarsa: {$this->document->expired_date->format('d/m/Y')}).";
    }
}
