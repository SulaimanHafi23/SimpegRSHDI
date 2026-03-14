<?php

namespace App\Exports;

use App\Models\LeaveRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveRecapSummarySheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function collection()
    {
        $leaves = LeaveRequest::with(['worker.department', 'leaveType', 'approver'])
            ->when(!empty($this->filters['worker_id']), fn ($query) => $query->where('worker_id', $this->filters['worker_id']))
            ->when(!empty($this->filters['date_from']), fn ($query) => $query->whereDate('start_date', '>=', $this->filters['date_from']))
            ->when(!empty($this->filters['date_to']), fn ($query) => $query->whereDate('start_date', '<=', $this->filters['date_to']))
            ->when(!empty($this->filters['status']), fn ($query) => $query->where('status', $this->filters['status']))
            ->when(!empty($this->filters['leave_type_id']), fn ($query) => $query->where('leave_type_id', $this->filters['leave_type_id']))
            ->when(!empty($this->filters['department_id']), function ($query) {
                $query->whereHas('worker', function ($workerQuery) {
                    $workerQuery->where('department_id', $this->filters['department_id']);
                });
            })
            ->orderBy('start_date', 'desc')
            ->get();

        return $leaves
            ->groupBy('worker_id')
            ->values()
            ->map(function ($items, $index) {
                $worker = $items->first()->worker;

                return [
                    'no' => $index + 1,
                    'nip' => $worker->nip ?? '-',
                    'nama' => $worker->name ?? '-',
                    'departemen' => $worker->department->name ?? '-',
                    'total_pengajuan' => $items->count(),
                    'pending' => $items->where('status', 'pending')->count(),
                    'approved' => $items->where('status', 'approved')->count(),
                    'rejected' => $items->where('status', 'rejected')->count(),
                    'cancelled' => $items->where('status', 'cancelled')->count(),
                    'total_hari_disetujui' => $items->where('status', 'approved')->sum(function ($leave) {
                        return (float) ($leave->total_days ?? 0);
                    }),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Pegawai',
            'Departemen',
            'Total Pengajuan',
            'Pending',
            'Disetujui',
            'Ditolak',
            'Dibatalkan',
            'Total Hari Disetujui',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
        ]);

        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A2:J' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'Rekap Pegawai';
    }
}
