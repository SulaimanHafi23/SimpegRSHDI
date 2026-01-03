<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkersTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                '123456',
                'John Doe',
                'john.doe@example.com',
                '081234567890',
                'Laki-laki',
                'Islam',
                '01/01/1990',
                'Jl. Contoh No. 123',
                'IT',
                'Staff',
                'Kontrak',
                '01/01/2024',
                'password123'
            ],
            [
                '123457',
                'Jane Smith',
                'jane.smith@example.com',
                '081234567891',
                'Perempuan',
                'Kristen',
                '15/05/1992',
                'Jl. Sample No. 456',
                'HR',
                'Staff',
                'Tetap',
                '15/03/2023',
                'password123'
            ],
        ];
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
            'Jabatan',
            'Status Kepegawaian',
            'Tanggal Bergabung',
            'Password',
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
}
