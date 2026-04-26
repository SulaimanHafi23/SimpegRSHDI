<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Models\Notification;
use App\Models\Worker;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessTripApprovalController extends Controller
{
    use DepartmentFilterable;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:business-trip.approve');
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

        $trips = $query->orderBy('start_date', 'desc')
            ->paginate($filters['per_page'])
            ->appends($filters);

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

        $departmentId = $this->getManagerDepartmentFilter();
        if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
            abort(403, 'Unauthorized');
        }

        return view('approvals.business-trips.show', compact('trip'));
    }

    public function approve(string $id)
    {
        $trip = BusinessTrip::findOrFail($id);

        if (empty($trip->supporting_document_path)) {
            return back()->with('error', 'Permohonan tidak dapat disetujui karena tidak memiliki lampiran surat tugas/disposisi.');
        }

        // Department restriction applies only for manager-scoped users.
        $departmentId = $this->getManagerDepartmentFilter();
        if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui permohonan ini.');
        }

        $user = Auth::user();

        $trip->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        if ($trip->worker?->user) {
            $this->notifyBusinessTripApproved($trip->worker->user->id, [
                'id' => $trip->id,
                'destination' => $trip->destination,
            ]);
        }

        return redirect()->route('approvals.business-trips.index')->with('success', 'Permohonan perjalanan dinas disetujui.');
    }

    public function reject(Request $request, string $id)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $trip = BusinessTrip::findOrFail($id);

        $departmentId = $this->getManagerDepartmentFilter();
        if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menolak permohonan ini.');
        }

        $user = Auth::user();

        $trip->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        if ($trip->worker?->user) {
            $this->notifyBusinessTripRejected($trip->worker->user->id, [
                'id' => $trip->id,
                'destination' => $trip->destination,
            ], $request->rejection_reason);
        }

        return redirect()->route('approvals.business-trips.index')->with('success', 'Permohonan perjalanan dinas ditolak.');
    }

    public function destroy(string $id)
    {
        $trip = BusinessTrip::findOrFail($id);

        $departmentId = $this->getManagerDepartmentFilter();
        if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus permohonan ini.');
        }

        $trip->delete();

        return redirect()->route('approvals.business-trips.index')->with('success', 'Permohonan perjalanan dinas berhasil dihapus.');
    }

    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'excel');
            $user = Auth::user();

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
                $startDate = $request->input('date_from');
                $endDate = $request->input('date_to');
            }

            $filters = [
                'worker_id' => $request->input('worker_id'),
                'date_from' => $startDate,
                'date_to' => $endDate,
                'status' => $request->input('status'),
                'month' => $month,
                'year' => $year,
            ];

            // Department filter applies only for manager-scoped users.
            $filters['department_id'] = $this->getManagerDepartmentFilter();

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

            $dateFrom = $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d F Y') : 'Semua';
            $dateTo = $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d F Y') : 'Semua';

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

    private function notifyBusinessTripApproved(string $userId, array $tripData): void
    {
        Notification::create([
            'user_id' => $userId,
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $userId,
            'type' => 'business_trip_approved',
            'title' => 'Perjalanan Dinas Disetujui',
            'message' => sprintf(
                'Permohonan perjalanan dinas ke %s telah disetujui.',
                $tripData['destination']
            ),
            'data' => [
                'business_trip_id' => $tripData['id'],
                'type' => 'business_trip',
                'action' => 'approved',
            ],
        ]);
    }

    private function notifyBusinessTripRejected(string $userId, array $tripData, ?string $reason = null): void
    {
        $message = sprintf(
            'Permohonan perjalanan dinas ke %s telah ditolak.',
            $tripData['destination']
        );

        if ($reason) {
            $message .= ' Alasan: ' . $reason;
        }

        Notification::create([
            'user_id' => $userId,
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $userId,
            'type' => 'business_trip_rejected',
            'title' => 'Perjalanan Dinas Ditolak',
            'message' => $message,
            'data' => [
                'business_trip_id' => $tripData['id'],
                'type' => 'business_trip',
                'action' => 'rejected',
                'reason' => $reason,
            ],
        ]);
    }
}
