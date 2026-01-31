<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class AttendanceStatsExport implements WithMultipleSheets
{
    protected $worker;
    protected $attendances;
    protected $stats;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($worker, $attendances, $stats, $dateFrom, $dateTo)
    {
        $this->worker = $worker;
        $this->attendances = $attendances;
        $this->stats = $stats;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function sheets(): array
    {
        return [
            new AttendanceStatsSummarySheet($this->worker, $this->stats, $this->dateFrom, $this->dateTo),
            new AttendanceStatsDetailSheet($this->worker, $this->attendances, $this->dateFrom, $this->dateTo),
        ];
    }
}

class AttendanceStatsSummarySheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $worker;
    protected $stats;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($worker, $stats, $dateFrom, $dateTo)
    {
        $this->worker = $worker;
        $this->stats = $stats;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'Ringkasan Statistik';
    }

    public function headings(): array
    {
        return [
            ['LAPORAN STATISTIK KEHADIRAN'],
            ['RS HAJI DARJAD IBRAHIM'],
            [''],
            ['Nama Pegawai:', $this->worker->name],
            ['NIP:', $this->worker->nip ?? '-'],
            ['Departemen:', $this->worker->department->name ?? '-'],
            ['Periode:', Carbon::parse($this->dateFrom)->format('d M Y') . ' - ' . Carbon::parse($this->dateTo)->format('d M Y')],
            ['Dicetak:', now()->format('d M Y H:i:s')],
            [''],
            ['KATEGORI', 'JUMLAH', 'KETERANGAN'],
        ];
    }

    public function collection()
    {
        return collect([
            ['Total Hari Kerja', $this->stats['total_work_days'], 'Hari kerja dalam periode'],
            ['Total Hadir', $this->stats['total_present'], $this->stats['attendance_percentage'] . '% kehadiran'],
            ['Total Absent', $this->stats['total_absent'], $this->stats['absence_percentage'] . '% ketidakhadiran'],
            ['Check In + Check Out', $this->stats['complete_attendance'], 'Absensi lengkap'],
            ['Check In Saja', $this->stats['check_in_only'], 'Tanpa check out'],
            ['Check Out Saja', $this->stats['check_out_only'], 'Tanpa check in'],
            ['Keterlambatan', $this->stats['late_arrivals'], 'Datang terlambat'],
            ['Pulang Awal', $this->stats['early_departures'], 'Pulang lebih awal'],
            ['Total Lembur (Jam)', $this->stats['overtime_hours'], 'Jam lembur'],
            [''],
            ['CUTI & IZIN', '', ''],
            ['Cuti', $this->stats['leave_days'], 'Hari cuti'],
            ['Sakit', $this->stats['sick_days'], 'Hari sakit'],
            ['Izin', $this->stats['permission_days'], 'Hari izin'],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 30,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for title
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');

        // Title style
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '1E40AF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:C2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Header row (row 10)
        $sheet->getStyle('A10:C10')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Data rows
        $lastRow = 10 + 14; // 14 data rows
        $sheet->getStyle('A11:C' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Section header style (row 21 - CUTI & IZIN)
        $sheet->getStyle('A21:C21')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8B5CF6'],
            ],
        ]);

        // Info rows style
        $sheet->getStyle('A4:A8')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        return [];
    }
}

class AttendanceStatsDetailSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $worker;
    protected $attendances;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($worker, $attendances, $dateFrom, $dateTo)
    {
        $this->worker = $worker;
        $this->attendances = $attendances;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'Detail Kehadiran';
    }

    public function headings(): array
    {
        return [
            ['DETAIL RIWAYAT KEHADIRAN - ' . $this->worker->name],
            ['Periode: ' . Carbon::parse($this->dateFrom)->format('d M Y') . ' - ' . Carbon::parse($this->dateTo)->format('d M Y')],
            [''],
            ['No', 'Tanggal', 'Hari', 'Check In', 'Check Out', 'Status', 'Terlambat', 'Pulang Awal', 'Keterangan'],
        ];
    }

    public function collection()
    {
        $data = [];
        $no = 1;

        foreach ($this->attendances as $attendance) {
            $status = match($attendance->status) {
                'present' => 'Hadir',
                'absent' => 'Tidak Hadir',
                'leave', 'cuti' => 'Cuti',
                'sick', 'sakit' => 'Sakit',
                'permission', 'izin' => 'Izin',
                default => ucfirst($attendance->status),
            };

            $data[] = [
                $no++,
                Carbon::parse($attendance->attendance_date)->format('d M Y'),
                Carbon::parse($attendance->attendance_date)->locale('id')->isoFormat('dddd'),
                $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '-',
                $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '-',
                $status,
                $attendance->is_late ? 'Ya' : '-',
                $attendance->is_early_leave ? 'Ya' : '-',
                $attendance->notes ?? '-',
            ];
        }

        return collect($data);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 15,
            'G' => 12,
            'H' => 12,
            'I' => 30,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for title
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

        // Title style
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '1E40AF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => [
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Header row (row 4)
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Data rows
        $lastRow = 4 + $this->attendances->count();
        if ($lastRow > 4) {
            $sheet->getStyle('A5:I' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alternate row colors
            for ($row = 5; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6'],
                        ],
                    ]);
                }
            }
        }

        return [];
    }
}
