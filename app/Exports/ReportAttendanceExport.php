<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ReportAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $filters;

    public function __construct(Collection $data, array $filters = [])
    {
        $this->data = $data;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIP',
            'Nama Pegawai',
            'Shift',
            'Lokasi',
            'Check In',
            'Check Out',
            'Status',
            'Terlambat',
            'Menit Terlambat',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->attendance_date?->format('Y-m-d') ?? '-',
            $attendance->worker->nip ?? '-',
            $attendance->worker->name ?? '-',
            $attendance->shift->name ?? '-',
            config('attendance.location.name', '-'),
            $attendance->check_in?->format('H:i:s') ?? '-',
            $attendance->check_out?->format('H:i:s') ?? '-',
            ucfirst($attendance->status ?? '-'),
            $attendance->is_late ? 'Ya' : 'Tidak',
            $attendance->late_minutes ?? 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16A34A'],
                ],
            ],
        ];
    }
}
