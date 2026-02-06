<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Traits\DepartmentFilterable;
use App\Models\BusinessTrip;
use App\Models\Worker;
use Illuminate\Http\Request;

class BusinessTripApprovalController extends Controller
{
    use DepartmentFilterable;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Manager|HR|Super Admin');
    }

    public function index(Request $request)
    {
        $departmentId = $this->getManagerDepartmentFilter();

        $filters = [
            'status' => $request->input('status', ''),
            'worker_id' => $request->input('worker_id'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'department_id' => $departmentId,
            'per_page' => 15,
        ];

        $query = BusinessTrip::with(['worker.user', 'worker.department']);

        // Apply department filter if Manager
        if ($departmentId) {
            $query->whereHas('worker', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        // Apply worker filter
        if ($filters['worker_id']) {
            $query->where('worker_id', $filters['worker_id']);
        }

        // Apply month filter
        if ($filters['month']) {
            $query->whereMonth('start_date', $filters['month']);
        }

        // Apply year filter
        if ($filters['year']) {
            $query->whereYear('start_date', $filters['year']);
        }

        $trips = $query->orderBy('start_date', 'desc')->paginate($filters['per_page']);

        // Calculate statistics
        $statsQuery = BusinessTrip::query();
        if ($departmentId) {
            $statsQuery->whereHas('worker', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $statistics = [
            'total' => $statsQuery->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
            'cancelled' => (clone $statsQuery)->where('status', 'cancelled')->count(),
        ];

        // Get workers for filter
        if ($departmentId) {
            $workers = Worker::where('department_id', $departmentId)->orderBy('name')->get();
        } else {
            $workers = Worker::orderBy('name')->get();
        }

        return view('approvals.business-trips.index', compact('trips', 'statistics', 'workers'));
    }

    public function show(string $id)
    {
        $trip = BusinessTrip::with(['worker.user', 'worker.department', 'approvedBy'])->findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('Manager') && $user->worker && $trip->worker->department_id !== $user->worker->department_id) {
            abort(403, 'Unauthorized');
        }

        return view('approvals.business-trips.show', compact('trip'));
    }

    public function approve(Request $request, string $id)
    {
        $request->validate(['approval_notes' => 'nullable|string|max:1000']);

        $trip = BusinessTrip::findOrFail($id);

        // permission check for manager
        $user = auth()->user();
        if ($user->hasRole('Manager') && $user->worker && $trip->worker->department_id !== $user->worker->department_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui permohonan ini.');
        }

        $trip->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('approvals.business-trips.index')->with('success', 'Permohonan perjalanan dinas disetujui.');
    }

    public function reject(Request $request, string $id)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $trip = BusinessTrip::findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('Manager') && $user->worker && $trip->worker->department_id !== $user->worker->department_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menolak permohonan ini.');
        }

        $trip->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('approvals.business-trips.index')->with('success', 'Permohonan perjalanan dinas ditolak.');
    }

    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'excel');
            $user = auth()->user();

            $filters = [
                'worker_id' => $request->input('worker_id'),
                'date_from' => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
                'date_to' => $request->input('date_to', now()->endOfMonth()->format('Y-m-d')),
                'status' => $request->input('status'),
            ];

            // Department filter for Manager
            if ($user->hasRole('Manager') && $user->worker) {
                $filters['department_id'] = $user->worker->department_id;
            }

            $query = BusinessTrip::with(['worker.department', 'approvedBy']);

            if ($filters['worker_id']) {
                $query->where('worker_id', $filters['worker_id']);
            }
            if ($filters['date_from']) {
                $query->whereDate('start_date', '>=', $filters['date_from']);
            }
            if ($filters['date_to']) {
                $query->whereDate('start_date', '<=', $filters['date_to']);
            }
            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['department_id'])) {
                $query->whereHas('worker', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            }

            $trips = $query->orderBy('start_date', 'desc')->get();

            $dateFrom = \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y');
            $dateTo = \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y');

            $filename = 'laporan-perjalanan-dinas-' . now()->format('Y-m-d-His');

            switch ($format) {
                case 'pdf':
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.business-trip-pdf', [
                        'trips' => $trips,
                        'dateFrom' => $dateFrom,
                        'dateTo' => $dateTo,
                        'status' => $filters['status'],
                    ]);
                    $pdf->setPaper('a4', 'landscape');
                    return $pdf->download($filename . '.pdf');

                case 'csv':
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\BusinessTripExport($filters),
                        $filename . '.csv',
                        \Maatwebsite\Excel\Excel::CSV
                    );

                default:
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\BusinessTripExport($filters),
                        $filename . '.xlsx'
                    );
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }
}
