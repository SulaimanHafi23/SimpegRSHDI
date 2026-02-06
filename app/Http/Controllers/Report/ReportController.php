<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceService;
use App\Services\Leave\LeaveRequestService;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\WorkerDocument\WorkerDocumentService;
use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Location;
use App\Models\DocumentType;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportAttendanceExport;
use App\Exports\ReportLeavesExport;
use App\Exports\ReportOvertimesExport;
use App\Exports\ReportWorkerDocumentsExport;

class ReportController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected LeaveRequestService $leaveService,
        protected OvertimeRequestService $overtimeService,
        protected WorkerDocumentService $workerDocumentService
    ) {
        $this->middleware('auth');
        // Allow viewing reports and exporting reports (some users may have export permission)
        $this->middleware('permission:report.view');
    }

    public function attendance(Request $request)
    {
        $filters = [
            'date_from' => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'location_id' => $request->input('location_id'),
        ];

        $attendances = $this->attendanceService->getAll($filters);

        $workers = Worker::select('id', 'name')->orderBy('name')->get();
        $locations = Location::select('id', 'name')->orderBy('name')->get();

        if ($request->query('export') === 'csv') {
            $collection = $attendances instanceof \Illuminate\Contracts\Pagination\Paginator ? $attendances->getCollection() : $attendances;
            $filename = 'attendance_report_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($collection) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Worker', 'Date', 'Check In', 'Check Out', 'Location', 'Status', 'Is Late', 'Late Minutes', 'Overtime Minutes']);
                foreach ($collection as $row) {
                    fputcsv($out, [
                        $row->worker->name ?? '-',
                        $row->attendance_date?->format('Y-m-d') ?? '-',
                        $row->check_in?->format('Y-m-d H:i:s') ?? '-',
                        $row->check_out?->format('Y-m-d H:i:s') ?? '-',
                        $row->location?->name ?? '-',
                        $row->status ?? '-',
                        $row->is_late ? 'Yes' : 'No',
                        $row->late_minutes ?? 0,
                        $row->overtime_minutes ?? 0,
                    ]);
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('admin.reports.attendance', compact('attendances', 'filters', 'workers', 'locations'));
    }

    public function leaves(Request $request)
    {
        $filters = [
            'date_from' => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
        ];

        $leaves = $this->leaveService->getAll($filters);

        return view('admin.reports.leaves', compact('leaves', 'filters'));
    }

    public function overtimes(Request $request)
    {
        $filters = [
            'date_from' => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
        ];

        $overtimes = $this->overtimeService->getAll($filters);

        return view('admin.reports.overtimes', compact('overtimes', 'filters'));
    }

    public function workerDocuments(Request $request)
    {
        $filters = [
            'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'document_type_id' => $request->input('document_type_id'),
            'status' => $request->input('status'),
        ];

        $documents = $this->workerDocumentService->getAll($filters);

        $workers = Worker::select('id', 'name')->orderBy('name')->get();
        $documentTypes = DocumentType::select('id', 'name')->orderBy('name')->get();

        if ($request->query('export') === 'csv') {
            $collection = $documents instanceof \Illuminate\Contracts\Pagination\Paginator ? $documents->getCollection() : $documents;
            $filename = 'worker_documents_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($collection) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Worker', 'Document Type', 'File', 'Issued At', 'Expired At', 'Status']);
                foreach ($collection as $row) {
                    fputcsv($out, [
                        $row->worker->name ?? '-',
                        $row->documentType->name ?? '-',
                        $row->file_path ? basename($row->file_path) : '-',
                        $row->issued_at?->format('Y-m-d') ?? '-',
                        $row->expired_at?->format('Y-m-d') ?? '-',
                        $row->status ?? '-',
                    ]);
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('admin.reports.worker-documents', compact('documents', 'filters', 'workers', 'documentTypes'));
    }

    public function workers(Request $request)
    {
        $filters = [
            'department_id' => $request->input('department_id'),
            'status' => $request->input('status'),
            'employment_status' => $request->input('employment_status'),
            'search' => $request->input('search'),
        ];

        $query = Worker::with('department');
        if (!empty($filters['department_id'])) $query->where('department_id', $filters['department_id']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['employment_status'])) $query->where('employment_status', $filters['employment_status']);
        if (!empty($filters['search'])) $query->where(function($q) use ($filters) {
            $q->where('name', 'like', "%{$filters['search']}%")
              ->orWhere('nip', 'like', "%{$filters['search']}%");
        });

        $workers = $query->orderBy('name')->paginate(20);

        if ($request->query('export') === 'csv') {
            $collection = $workers instanceof \Illuminate\Contracts\Pagination\Paginator ? $workers->getCollection() : $workers;
            $filename = 'workers_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($collection) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Name', 'NIP', 'Email', 'Department', 'Employment Status', 'Status']);
                foreach ($collection as $w) {
                    fputcsv($out, [
                        $w->name,
                        $w->nip,
                        $w->email,
                        $w->department->name ?? '-',
                        $w->employment_status ?? '-',
                        $w->status ?? '-',
                    ]);
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        $departments = \App\Models\Department::select('id','name')->orderBy('name')->get();

        return view('admin.reports.workers', compact('workers', 'filters', 'departments'));
    }

    /**
     * Export Attendance Report
     */
    public function exportAttendance(Request $request)
    {
        $filters = [
            'date_from' => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'location_id' => $request->input('location_id'),
        ];

        $attendances = $this->attendanceService->getAll($filters);
        $collection = $attendances instanceof \Illuminate\Contracts\Pagination\Paginator ? $attendances->getCollection() : collect($attendances);

        $format = $request->input('format', 'pdf');
        $filename = 'laporan-presensi-' . now()->format('Y-m-d-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.report-attendance-pdf', [
                'attendances' => $collection,
                'filters' => $filters,
            ]);
            return $pdf->download($filename . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new ReportAttendanceExport($collection, $filters), $filename . '.xlsx');
        } else {
            return Excel::download(new ReportAttendanceExport($collection, $filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
    }

    /**
     * Export Leaves Report
     */
    public function exportLeaves(Request $request)
    {
        $filters = [
            'date_from' => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
        ];

        $leaves = $this->leaveService->getAll($filters);
        $collection = $leaves instanceof \Illuminate\Contracts\Pagination\Paginator ? $leaves->getCollection() : collect($leaves);

        $format = $request->input('format', 'pdf');
        $filename = 'laporan-cuti-' . now()->format('Y-m-d-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.report-leaves-pdf', [
                'leaves' => $collection,
                'filters' => $filters,
            ]);
            return $pdf->download($filename . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new ReportLeavesExport($collection, $filters), $filename . '.xlsx');
        } else {
            return Excel::download(new ReportLeavesExport($collection, $filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
    }

    /**
     * Export Overtimes Report
     */
    public function exportOvertimes(Request $request)
    {
        $filters = [
            'date_from' => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
        ];

        $overtimes = $this->overtimeService->getAll($filters);
        $collection = $overtimes instanceof \Illuminate\Contracts\Pagination\Paginator ? $overtimes->getCollection() : collect($overtimes);

        $format = $request->input('format', 'pdf');
        $filename = 'laporan-lembur-' . now()->format('Y-m-d-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.report-overtimes-pdf', [
                'overtimes' => $collection,
                'filters' => $filters,
            ]);
            return $pdf->download($filename . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new ReportOvertimesExport($collection, $filters), $filename . '.xlsx');
        } else {
            return Excel::download(new ReportOvertimesExport($collection, $filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
    }

    /**
     * Export Worker Documents Report
     */
    public function exportWorkerDocuments(Request $request)
    {
        $filters = [
            'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'document_type_id' => $request->input('document_type_id'),
            'status' => $request->input('status'),
        ];

        $documents = $this->workerDocumentService->getAll($filters);
        $collection = $documents instanceof \Illuminate\Contracts\Pagination\Paginator ? $documents->getCollection() : collect($documents);

        $format = $request->input('format', 'pdf');
        $filename = 'laporan-dokumen-pegawai-' . now()->format('Y-m-d-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.report-worker-documents-pdf', [
                'documents' => $collection,
                'filters' => $filters,
            ]);
            return $pdf->download($filename . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new ReportWorkerDocumentsExport($collection, $filters), $filename . '.xlsx');
        } else {
            return Excel::download(new ReportWorkerDocumentsExport($collection, $filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
    }
}
