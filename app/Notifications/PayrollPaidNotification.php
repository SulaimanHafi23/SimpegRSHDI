<?php

namespace App\Notifications;

use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Payroll $payroll)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $period    = $this->payroll->payrollPeriod;
        $periodName = $period ? $period->month_name : '-';
        $net       = number_format((float) $this->payroll->net_salary, 0, ',', '.');

        return (new MailMessage)
            ->subject("Slip Gaji {$periodName} Tersedia")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Slip gaji Anda untuk periode {$periodName} sudah tersedia.")
            ->line("Gaji Bersih: Rp {$net}")
            ->action('Lihat Slip Gaji', route('employee.payrolls.show', $this->payroll->id))
            ->line('Terima kasih.');
    }

    public function toArray(object $notifiable): array
    {
        $period = $this->payroll->payrollPeriod;

        return [
            'type'       => 'payroll_paid',
            'payroll_id' => $this->payroll->id,
            'period'     => $period?->month_name,
            'net_salary' => (float) $this->payroll->net_salary,
            'paid_at'    => $this->payroll->paid_at?->toDateString(),
            'message'    => 'Slip gaji periode ' . ($period?->month_name ?? '-') . ' sudah tersedia.',
        ];
    }
}
