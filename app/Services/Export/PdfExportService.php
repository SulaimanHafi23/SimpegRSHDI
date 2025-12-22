<?php

namespace App\Services\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PdfExportService
{
    /**
     * Export attendance report to PDF
     */
    public function exportAttendanceReport($attendances, $worker, $filters)
    {
        $collection = collect($attendances);
        
        $data = [
            'title' => 'Laporan Riwayat Absensi',
            'worker' => $worker,
            'attendances' => $attendances,
            'filters' => $filters,
            'generated_at' => now()->format('d F Y H:i'),
            'summary' => [
                'total' => $collection->count(),
                'present' => $collection->where('status', 'Hadir')->count(),
                'late' => $collection->where('status', 'Terlambat')->count(),
                'absent' => $collection->where('status', 'Tidak Hadir')->count(),
            ]
        ];

        $pdf = Pdf::loadView('employee.exports.attendance-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Absensi_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export leave report to PDF
     */
    public function exportLeaveReport($leaves, $worker, $filters)
    {
        $collection = collect($leaves);
        
        $data = [
            'title' => 'Laporan Riwayat Cuti',
            'worker' => $worker,
            'leaves' => $leaves,
            'filters' => $filters,
            'generated_at' => now()->format('d F Y H:i'),
            'summary' => [
                'total' => $collection->count(),
                'pending' => $collection->where('status', 'pending')->count(),
                'approved' => $collection->where('status', 'approved')->count(),
                'rejected' => $collection->where('status', 'rejected')->count(),
            ]
        ];

        $pdf = Pdf::loadView('employee.exports.leave-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Cuti_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export overtime report to PDF
     */
    public function exportOvertimeReport($overtimes, $worker, $filters)
    {
        $collection = collect($overtimes);
        
        $data = [
            'title' => 'Laporan Riwayat Lembur',
            'worker' => $worker,
            'overtimes' => $overtimes,
            'filters' => $filters,
            'generated_at' => now()->format('d F Y H:i'),
            'summary' => [
                'total' => $collection->count(),
                'pending' => $collection->where('status', 'pending')->count(),
                'approved' => $collection->where('status', 'approved')->count(),
                'rejected' => $collection->where('status', 'rejected')->count(),
                'total_hours' => $collection->where('status', 'approved')->sum('total_hours'),
            ]
        ];

        $pdf = Pdf::loadView('employee.exports.overtime-pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'Lembur_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }
}
