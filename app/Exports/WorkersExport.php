<?php

namespace App\Exports;

use App\Models\Worker;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Worker::with(['department', 'gender', 'religion']);

        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['employment_status'])) {
            $query->where('employment_status', $this->filters['employment_status']);
        }

        if (!empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama Lengkap',
            'Email',
            'Nomor Telepon',
            'Jenis Kelamin',
            'Agama',
            'Tanggal Lahir',
            'Alamat',
            'Departemen',
            'Status Kepegawaian',
            'Status',
            'Tanggal Bergabung',
            'Tanggal Resign',
        ];
    }

    public function map($worker): array
    {
        return [
            $worker->nip,
            $worker->name,
            $worker->email,
            $worker->phone_number,
            $worker->gender->name ?? '-',
            $worker->religion->name ?? '-',
            $worker->birth_date ? $worker->birth_date->format('d/m/Y') : '-',
            $worker->address ?? '-',
            $worker->department->name ?? '-',
            $this->getEmploymentStatusLabel($worker->employment_status),
            $this->getStatusLabel($worker->status),
            $worker->hire_date ? $worker->hire_date->format('d/m/Y') : '-',
            $worker->resign_date ? $worker->resign_date->format('d/m/Y') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    protected function getEmploymentStatusLabel($status)
    {
        return match($status) {
            'permanent' => 'Tetap',
            'contract' => 'Kontrak',
            'probation' => 'Masa Percobaan',
            'intern' => 'Magang',
            default => $status
        };
    }

    protected function getStatusLabel($status)
    {
        return match($status) {
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'resigned' => 'Resign',
            default => $status
        };
    }
}
