<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceService;
use App\Services\Leave\LeaveRequestService;
use App\Services\Overtime\OvertimeRequestService;
use App\Services\WorkerDocument\WorkerDocumentService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly LeaveRequestService $leaveService,
        private readonly OvertimeRequestService $overtimeService,
        private readonly WorkerDocumentService $workerDocumentService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-reports');
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

        return view('admin.reports.attendance', compact('attendances', 'filters'));
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
            'date_from' => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
            'worker_id' => $request->input('worker_id'),
            'document_type_id' => $request->input('document_type_id'),
            'status' => $request->input('status'),
        ];

        $documents = $this->workerDocumentService->getAll($filters);

        return view('admin.reports.worker-documents', compact('documents', 'filters'));
    }
}
