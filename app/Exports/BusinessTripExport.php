<?php

namespace App\Exports;

use App\Models\BusinessTrip;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BusinessTripExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected array $filters;

    protected ?Collection $trips = null;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->getTrips();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Pegawai',
            'Departemen',
            'Tujuan',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi',
            'Estimasi Biaya',
            'Status',
            'Disetujui Oleh',
            'Tujuan Perjalanan',
        ];
    }

    public function map($trip): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $trip->worker->nip ?? '-',
            $trip->worker->name ?? '-',
            $trip->worker->department->name ?? '-',
            $trip->destination ?? '-',
            $trip->start_date?->format('d/m/Y') ?? '-',
            $trip->end_date?->format('d/m/Y') ?? '-',
            $trip->duration_label,
            (float) ($trip->estimated_cost ?? 0),
            $this->getStatusLabel($trip->status),
            $trip->approvedBy->name ?? '-',
            $trip->purpose ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '8B5CF6'],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => '"Rp" #,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $totalRow = $this->getTrips()->count() + 3;
                $sheet = $event->sheet;

                $sheet->setCellValue('H' . $totalRow, 'Total Estimasi Biaya');
                $sheet->setCellValue('I' . $totalRow, (float) $this->getTrips()->sum('estimated_cost'));

                $sheet->getStyle('H' . $totalRow . ':I' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EDE9FE'],
                    ],
                ]);
                $sheet->getStyle('I' . $totalRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');
            },
        ];
    }

    protected function getTrips(): Collection
    {
        if ($this->trips !== null) {
            return $this->trips;
        }

        $query = BusinessTrip::with(['worker.department', 'approvedBy']);

        if (!empty($this->filters['worker_id'])) {
            $query->where('worker_id', $this->filters['worker_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('start_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('start_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['department_id'])) {
            $query->whereHas('worker', function ($q) {
                $q->where('department_id', $this->filters['department_id']);
            });
        }

        return $this->trips = $query->orderBy('start_date', 'desc')->get();
    }

    protected function getStatusLabel($status)
    {
        return match ($status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }
}
