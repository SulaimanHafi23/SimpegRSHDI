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
        try {
            $collection = collect($attendances);
            
            $data = [
                'title' => 'Laporan Riwayat Absensi',
                'worker' => $worker,
                'attendances' => $attendances,
                'filters' => $filters,
                'generated_at' => now()->format('d F Y H:i'),
                'summary' => [
                    'total' => $collection->count(),
                    'present' => $collection->where('status', 'present')->count(),
                    'late' => $collection->where('status', 'late')->count(),
                    'absent' => $collection->where('status', 'absent')->count(),
                ]
            ];

            $pdf = Pdf::loadView('employee.exports.attendance-pdf', $data);
            $pdf->setPaper('a4', 'portrait');
            
            $filename = 'Absensi_' . $worker->name . '_' . now()->format('YmdHis') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            // If GD extension is not available or other PDF error occurs
            if (strpos($e->getMessage(), 'GD extension') !== false) {
                throw new \Exception('Error: PHP GD Extension tidak ter-install. Hubungi administrator server untuk install PHP GD extension.');
            }
            throw $e;
        }
    }

    /**
     * Export leave report to PDF
     */
    public function exportLeaveReport($leaves, $worker, $filters)
    {
        try {
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
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'GD extension') !== false) {
                throw new \Exception('Error: PHP GD Extension tidak ter-install. Hubungi administrator server untuk install PHP GD extension.');
            }
            throw $e;
        }
    }

    /**
     * Export overtime report to PDF
     */
    public function exportOvertimeReport($overtimes, $worker, $filters)
    {
        try {
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
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'GD extension') !== false) {
                throw new \Exception('Error: PHP GD Extension tidak ter-install. Hubungi administrator server untuk install PHP GD extension.');
            }
            throw $e;
        }
    }
}
