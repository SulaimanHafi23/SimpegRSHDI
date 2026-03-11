<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Document\DocumentExpiryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DocumentExpiryController extends Controller
{
    protected DocumentExpiryService $documentExpiryService;

    public function __construct(DocumentExpiryService $documentExpiryService)
    {
        $this->middleware('auth');
        $this->middleware('permission:dashboard.admin|documents.view');

        $this->documentExpiryService = $documentExpiryService;
    }

    /**
     * Display a listing of expiring documents.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $documentsByUrgency = $this->documentExpiryService->getDocumentsByUrgency();
        $stats = $this->documentExpiryService->getExpiryStatistics();

        // Filter documents based on selected filter
        $documents = match($filter) {
            'critical' => $documentsByUrgency['critical'],
            'urgent' => $documentsByUrgency['urgent'],
            'warning' => $documentsByUrgency['warning'],
            'watch' => $documentsByUrgency['watch'],
            default => collect([
                ...$documentsByUrgency['critical'],
                ...$documentsByUrgency['urgent'],
                ...$documentsByUrgency['warning'],
                ...$documentsByUrgency['watch'],
            ])->sortBy('expired_date')
        };

        return view('admin.document-expiry.index', compact(
            'documents',
            'documentsByUrgency',
            'stats',
            'filter'
        ));
    }

    /**
     * Display statistics dashboard for document expiry.
     */
    public function statistics()
    {
        $stats = $this->documentExpiryService->getExpiryStatistics();
        $documentsByUrgency = $this->documentExpiryService->getDocumentsByUrgency();
        $criticalWorkers = $this->documentExpiryService->getWorkersWithExpiredCriticalDocuments();

        return view('admin.document-expiry.statistics', compact(
            'stats',
            'documentsByUrgency',
            'criticalWorkers'
        ));
    }

    /**
     * Export expiring documents report.
     */
    public function export(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $documentsByUrgency = $this->documentExpiryService->getDocumentsByUrgency();

        $documents = match($filter) {
            'critical' => $documentsByUrgency['critical'],
            'urgent' => $documentsByUrgency['urgent'],
            'warning' => $documentsByUrgency['warning'],
            'watch' => $documentsByUrgency['watch'],
            default => collect([
                ...$documentsByUrgency['critical'],
                ...$documentsByUrgency['urgent'],
                ...$documentsByUrgency['warning'],
                ...$documentsByUrgency['watch'],
            ])->sortBy('expired_date')
        };

        // Create CSV export
        $filename = 'document-expiry-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($documents) {
            $file = fopen('php://output', 'w');

            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, [
                'No',
                'Nama Pegawai',
                'NIP',
                'Unit',
                'Jabatan',
                'Nama Dokumen',
                'Tanggal Kadaluarsa',
                'Hari Tersisa',
                'Status Urgensi'
            ]);

            // Data
            $no = 1;
            foreach ($documents as $document) {
                $daysUntilExpiry = now()->startOfDay()->diffInDays($document->expired_date, false);
                $urgencyLevel = $this->documentExpiryService->getUrgencyLevel($document);

                $urgencyText = match($urgencyLevel) {
                    'critical' => 'Kadaluarsa',
                    'urgent' => 'Sangat Mendesak',
                    'warning' => 'Perhatian',
                    'watch' => 'Pantau',
                    default => 'Normal'
                };

                $documentName = $document->documentType?->name
                    ?? $document->departmentDocumentType?->customDocumentType?->name
                    ?? '-';

                fputcsv($file, [
                    $no++,
                    $document->worker->name,
                    $document->worker->employee_id ?? '-',
                    $document->worker->unit?->name ?? '-',
                    $document->worker->position?->name ?? '-',
                    $documentName,
                    $document->expired_date->format('d/m/Y'),
                    $daysUntilExpiry . ' hari',
                    $urgencyText
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
