<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TodayAttendanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $workers;
    protected $stats;
    protected $date;

    public function __construct($workers, $stats, $date)
    {
        $this->workers = $workers;
        $this->stats = $stats;
        $this->date = $date;
    }

    public function collection()
    {
        return $this->workers->map(function ($worker, $index) {
            $shift = $worker->shift ?? $worker->workerShifts->first()?->shift;
            $shiftInfo = '-';
            if ($shift) {
                $schedule = $shift->getScheduleForDate($this->date);
                $shiftInfo = $shift->name . ' (' . \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') . '-' . \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') . ')';
            }
            
            // Jika ada leave request, tampilkan info cuti/sakit/izin
            if ($worker->leave_request) {
                $checkInDisplay = 'CUTI/IZIN';
                $checkOutDisplay = 'CUTI/IZIN';
                $lateDisplay = '-';
                $notesDisplay = $worker->leave_request->leaveType->name . ' (' . 
                    $worker->leave_request->start_date->format('d/m/Y') . ' - ' . 
                    $worker->leave_request->end_date->format('d/m/Y') . ')';
            } else {
                $checkInDisplay = $worker->check_in_time ?? '-';
                $checkOutDisplay = $worker->check_out_time ?? '-';
                $lateDisplay = $worker->is_late ? $worker->late_minutes . ' menit' : '-';
                $notesDisplay = $worker->today_attendance?->notes ?? '-';
            }
            
            return [
                'no' => $index + 1,
                'nip' => $worker->nip,
                'name' => $worker->name,
                'department' => $worker->department->name ?? '-',
                'shift' => $shiftInfo,
                'check_in' => $checkInDisplay,
                'check_out' => $checkOutDisplay,
                'status' => $worker->status_label,
                'late_minutes' => $lateDisplay,
                'notes' => $notesDisplay,
            ];
        });
    }

    public function headings(): array
    {
        $dateFormatted = \Carbon\Carbon::parse($this->date)->translatedFormat('l, d F Y');
        
        return [
            ['PEMERINTAH KABUPATEN TANAH LAUT'],
            ['RSUD HAJI DARLAN ISMAIL'],
            ['Bumi Harapan, Kec. Bumi Makmur, Kabupaten Tanah Laut, Kalimantan Selatan'],
            ['Telp: (0511) 4774673 | Email: rsud.hdi@tapinkab.go.id'],
            [''],
            ['LAPORAN ABSENSI HARIAN'],
            ['Tanggal: ' . $dateFormatted],
            [''],
            ['RINGKASAN KEHADIRAN'],
            ['Total Pegawai: ' . $this->stats['total_workers'] . ' orang'],
            ['Hadir: ' . $this->stats['present'] . ' orang'],
            ['Terlambat: ' . $this->stats['late'] . ' orang'],
            ['Belum Absen: ' . $this->stats['not_checked_in'] . ' orang'],
            ['Libur Kerja: ' . ($this->stats['off_day'] ?? 0) . ' orang'],
            ['Cuti: ' . $this->stats['leave'] . ' orang'],
            ['Sakit: ' . $this->stats['sick'] . ' orang'],
            ['Izin: ' . $this->stats['permission'] . ' orang'],
            [''],
            ['DATA DETAIL ABSENSI'],
            ['No', 'NIP', 'Nama Pegawai', 'Departemen', 'Shift', 'Check In', 'Check Out', 'Status', 'Keterlambatan', 'Catatan']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Letterhead - Row 1
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Letterhead - Row 2
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1a5490']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Letterhead - Row 3 & 4
        $sheet->mergeCells('A3:J3');
        $sheet->mergeCells('A4:J4');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Title
        $sheet->mergeCells('A6:J6');
        $sheet->getStyle('A6')->applyFromArray([
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 16],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);

        // Subtitle (Date)
        $sheet->mergeCells('A7:J7');
        $sheet->getStyle('A7')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ]);

        // Summary section header (row 9)
        $sheet->mergeCells('A9:J9');
        $sheet->getStyle('A9')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6'],
            ],
        ]);

        // Summary data (rows 10-16)
        for ($row = 10; $row <= 16; $row++) {
            $sheet->mergeCells("A{$row}:J{$row}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2'],
                ],
            ]);
        }

        // Data section header (row 18)
        $sheet->mergeCells('A18:J18');
        $sheet->getStyle('A18')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6'],
            ],
        ]);

        // Table header (row 19)
        $sheet->getStyle('A19:J19')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Table data borders
        $lastRow = 19 + $this->workers->count();
        $sheet->getStyle("A19:J{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center align for specific columns
        $sheet->getStyle("A20:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F20:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // NIP
            'C' => 25,  // Nama
            'D' => 20,  // Departemen
            'E' => 25,  // Shift
            'F' => 12,  // Check In
            'G' => 12,  // Check Out
            'H' => 15,  // Status
            'I' => 15,  // Keterlambatan
            'J' => 30,  // Catatan
        ];
    }

    public function title(): string
    {
        return 'Absensi ' . \Carbon\Carbon::parse($this->date)->format('d-m-Y');
    }
}
