<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceService;
use App\Services\Leave\LeaveRequestService;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\WorkerDocument\WorkerDocumentService;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\DocumentType;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportAttendanceExport;
use App\Exports\ReportLeavesExport;
use App\Exports\ReportOvertimesExport;
use App\Exports\ReportWorkerDocumentsExport;

class ReportController extends Controller
{
    use DepartmentFilterable;

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
        $month = $request->input('month');
        $year = $request->input('year');
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        } else {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        }

        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'worker_id' => $request->input('worker_id'),
            'department_id' => $this->getManagerDepartmentFilter(),
            'month' => $month,
            'year' => $year,
        ];

        $attendances = $this->attendanceService->getAll($filters);

        $departmentId = $this->getManagerDepartmentFilter();
        $workers = Worker::select('id', 'name')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->orderBy('name')->get();

        if ($request->query('export') === 'csv') {
            $collection = $attendances instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($attendances->items()) : $attendances;
            $filename = 'attendance_report_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($collection) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Worker', 'Date', 'Check In', 'Check Out', 'Location', 'Status', 'Is Late', 'Late Minutes']);
                foreach ($collection as $row) {
                    fputcsv($out, [
                        $row->worker->name ?? '-',
                        $row->attendance_date?->format('Y-m-d') ?? '-',
                        $row->check_in?->format('Y-m-d H:i:s') ?? '-',
                        $row->check_out?->format('Y-m-d H:i:s') ?? '-',
                        config('attendance.location.name', '-'),
                        $row->status ?? '-',
                        $row->is_late ? 'Yes' : 'No',
                        $row->late_minutes ?? 0,
                    ]);
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('admin.reports.attendance', compact('attendances', 'filters', 'workers'));
    }

    public function leaves(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        } else {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        }

        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'worker_id' => $request->input('worker_id'),
            'leave_type_id' => $request->input('leave_type_id'),
            'status' => $request->input('status'),
            'department_id' => $this->getManagerDepartmentFilter(),
            'month' => $month,
            'year' => $year,
        ];

        $leaves = $this->leaveService->getAll($filters);

        $departmentId = $this->getManagerDepartmentFilter();
        $workers = Worker::select('id', 'name')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->orderBy('name')->get();
        $leaveTypes = \App\Models\LeaveType::select('id', 'name')->orderBy('name')->get();

        return view('admin.reports.leaves', compact('leaves', 'filters', 'workers', 'leaveTypes'));
    }

    public function overtimes(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        } else {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        }

        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'department_id' => $this->getManagerDepartmentFilter(),
            'month' => $month,
            'year' => $year,
        ];

        $overtimes = $this->overtimeService->getAll($filters);

        $departmentId = $this->getManagerDepartmentFilter();
        $workers = Worker::select('id', 'name')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->orderBy('name')->get();

        return view('admin.reports.overtimes', compact('overtimes', 'filters', 'workers'));
    }

    public function workerDocuments(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        } else {
            $startDate = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('date_to', now()->endOfMonth()->format('Y-m-d'));
        }

        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'worker_id' => $request->input('worker_id'),
            'document_type_id' => $request->input('document_type_id'),
            'status' => $request->input('status'),
            'department_id' => $this->getManagerDepartmentFilter(),
            'month' => $month,
            'year' => $year,
        ];

        $documents = $this->workerDocumentService->getAll($filters);

        $departmentId = $this->getManagerDepartmentFilter();
        $workers = Worker::select('id', 'name')
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->orderBy('name')->get();
        $documentTypes = DocumentType::select('id', 'name')->orderBy('name')->get();

        if ($request->query('export') === 'csv') {
            $collection = $documents instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($documents->items()) : $documents;
            $filename = 'worker_documents_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($collection) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Pegawai', 'Jenis Dokumen', 'Nama File', 'Tanggal Terbit', 'Tanggal Kedaluwarsa', 'Status']);
                foreach ($collection as $row) {
                    fputcsv($out, [
                        trim((string) ($row->worker->name ?? '-')),
                        trim((string) ($row->documentType->name ?? '-')),
                        trim((string) ($row->file_path ? basename($row->file_path) : '-')),
                        $row->issued_at?->format('d/m/Y') ?? '-',
                        $row->expired_at?->format('d/m/Y') ?? '-',
                        trim((string) ($row->status ?? '-')),
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
        $departmentId = $this->getManagerDepartmentFilter();

        $filters = [
            'department_id' => $departmentId ?? $request->input('department_id'),
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
            $collection = $workers instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($workers->items()) : $workers;
            $filename = 'workers_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($collection) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Nama', 'NIP', 'Email', 'Departemen', 'Status Kepegawaian', 'Status']);
                foreach ($collection as $w) {
                    fputcsv($out, [
                        trim((string) ($w->name ?? '-')),
                        trim((string) ($w->nip ?? '-')),
                        trim((string) ($w->email ?? '-')),
                        trim((string) ($w->department->name ?? '-')),
                        trim((string) ($w->employment_status ?? '-')),
                        trim((string) ($w->status ?? '-')),
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
        $month = $request->input('month');
        $year = $request->input('year');
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        } else {
            $startDate = $request->input('date_from') ?? $request->input('start_date');
            $endDate = $request->input('date_to') ?? $request->input('end_date');
        }

        // Get department filter: prioritas dari modal, fallback ke manager restriction
        $departmentFilter = $request->input('department_id') ?: $this->getManagerDepartmentFilter();

        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'worker_id' => $request->input('worker_id'),
            'department_id' => $departmentFilter,
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'month' => $month,
            'year' => $year,
        ];

        $attendances = $this->attendanceService->getAll($filters);
        $collection = $attendances instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($attendances->items()) : collect($attendances);

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
        $month = $request->input('month');
        $year = $request->input('year');
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        } else {
            $startDate = $request->input('date_from') ?? $request->input('start_date');
            $endDate = $request->input('date_to') ?? $request->input('end_date');
        }

        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'worker_id' => $request->input('worker_id'),
            'leave_type_id' => $request->input('leave_type_id'),
            'status' => $request->input('status'),
            'department_id' => $this->getManagerDepartmentFilter(),
            'month' => $month,
            'year' => $year,
        ];

        $leaves = $this->leaveService->getAll($filters);
        $collection = $leaves instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($leaves->items()) : collect($leaves);

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
        $month = $request->input('month');
        $year = $request->input('year');
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        } else {
            $startDate = $request->input('date_from') ?? $request->input('start_date');
            $endDate = $request->input('date_to') ?? $request->input('end_date');
        }

        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'worker_id' => $request->input('worker_id'),
            'status' => $request->input('status'),
            'department_id' => $this->getManagerDepartmentFilter(),
            'month' => $month,
            'year' => $year,
        ];

        $overtimes = $this->overtimeService->getAll($filters);
        $collection = $overtimes instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($overtimes->items()) : collect($overtimes);

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
        $month = $request->input('month');
        $year = $request->input('year');
        if ($month || $year) {
            $year = $year ?: now()->year;
            if ($month) {
                $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->format('Y-m-d');
                $endDate = \Carbon\Carbon::createFromDate($year, 1, 1)->endOfYear()->format('Y-m-d');
            }
        } else {
            $startDate = $request->input('date_from') ?? $request->input('start_date');
            $endDate = $request->input('date_to') ?? $request->input('end_date');
        }

        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'worker_id' => $request->input('worker_id'),
            'document_type_id' => $request->input('document_type_id'),
            'status' => $request->input('status'),
            'department_id' => $this->getManagerDepartmentFilter(),
            'month' => $month,
            'year' => $year,
        ];

        $documents = $this->workerDocumentService->getAll($filters);
        $collection = $documents instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($documents->items()) : collect($documents);

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
