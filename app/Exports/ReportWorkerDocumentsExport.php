<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ReportWorkerDocumentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            'NIP',
            'Nama Pegawai',
            'Jenis Dokumen',
            'Nama File',
            'Tanggal Upload',
            'Tanggal Kadaluarsa',
            'Status',
            'Diverifikasi Oleh',
            'Tanggal Verifikasi',
        ];
    }

    public function map($document): array
    {
        return [
            $document->worker->nip ?? '-',
            $document->worker->name ?? '-',
            $document->documentType->name ?? '-',
            $document->file_path ? basename($document->file_path) : '-',
            $document->created_at?->format('Y-m-d') ?? '-',
            $document->expired_date?->format('Y-m-d') ?? '-',
            $this->getStatusLabel($document->status),
            $document->verifier->name ?? '-',
            $document->verified_at?->format('Y-m-d') ?? '-',
        ];
    }

    protected function getStatusLabel($status): string
    {
        return match($status) {
            'pending' => 'Menunggu',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default => ucfirst($status ?? '-'),
        };
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
