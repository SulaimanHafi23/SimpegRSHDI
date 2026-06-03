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
        $user = Auth::user();
        $isHR = $user->hasRole(['hr', 'HR']) && !$user->hasRole(['admin', 'Super Admin']);
        $isAdmin = $user->hasRole(['admin', 'Super Admin', 'super admin', 'superadmin']);
        $isManager = $user->hasRole(['manager', 'Manager']) && !$isHR && !$isAdmin;
        $departmentId = $this->getManagerDepartmentFilter();

        // Determine default status
        if ($isManager) {
            $defaultStatus = 'all';
        } elseif ($isHR) {
            $defaultStatus = 'manager_verified';
        } else {
            $defaultStatus = 'all'; // Admin sees all by default
        }

        $status = $request->input('status');
        
        // Handle pagination where 'status' might be dropped if it was null ('all' selection)
        if ($status === null && $request->has('original_status')) {
            $status = $request->input('original_status');
        }
        
        if ($status === null) {
            if ($request->exists('status')) {
                $status = 'all'; 
            } else {
                $status = $defaultStatus;
            }
        }

        $displayStatus = $status;
        
        if ($status === 'all' || $status === '') {
            $status = null;
            $displayStatus = 'all';
        }

        // Role-based status constraints
        if ($isHR && !$isAdmin) {
            // HR cannot see pending requests
            if ($status === 'pending') {
                $status = 'manager_verified';
                $displayStatus = 'manager_verified';
            }
        }

        $filters = [
            'status' => $status,
            'original_status' => $displayStatus,
            'worker_id' => $request->input('worker_id'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'department_id' => $departmentId,
            'per_page' => 15,
            'hr_only_verified' => ($isHR && !$isAdmin), // Custom filter for HR
        ];

        $query = BusinessTrip::with(['worker.user', 'worker.department']);

        // Apply department filter if Manager
        if ($departmentId) {
            $query->whereHas('worker', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // Apply status filter
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        } elseif ($filters['hr_only_verified']) {
            // HR viewing 'all' should not see 'pending'
            $query->where('status', '!=', 'pending');
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
        
        if ($isHR && !$isAdmin) {
            $statsQuery->where('status', '!=', 'pending');
        }

        $statistics = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'verified' => (clone $statsQuery)->where('status', 'manager_verified')->count(),
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

        return view('approvals.business-trips.index', compact('trips', 'statistics', 'workers', 'isManager', 'isHR', 'isAdmin', 'filters'));
    }

    public function show(string $id)
    {
        $trip = BusinessTrip::with(['worker.user', 'worker.department', 'approvedBy', 'manager'])->findOrFail($id);

        $departmentId = $this->getManagerDepartmentFilter();
        if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
            abort(403, 'Unauthorized');
        }

        return view('approvals.business-trips.show', compact('trip'));
    }

    /**
     * Manager verifies the business trip request (first stage)
     */
    public function verify(string $id)
    {
        try {
            $trip = BusinessTrip::findOrFail($id);

            if (Auth::user()->hasRole('Super Admin')) {
                return back()->with('error', 'Super Admin hanya dapat menghapus data, tidak dapat melakukan verifikasi.');
            }



            // Department restriction applies only for manager-scoped users.
            $departmentId = $this->getManagerDepartmentFilter();
            if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
                return back()->with('error', 'Anda tidak memiliki akses untuk memverifikasi permohonan ini.');
            }

            // Only manager can verify pending requests
            $user = Auth::user();
            if ($user->hasRole(['admin', 'hr'])) {
                throw new \Exception('Hanya manager yang dapat melakukan verifikasi.');
            }

            if ($trip->status !== 'pending') {
                throw new \Exception('Hanya permohonan perjalanan dinas yang berstatus pending yang dapat diverifikasi.');
            }

            // Mark as manager_verified
            $trip->update([
                'status' => 'manager_verified',
                'manager_id' => $user->id,
                'manager_verified_at' => now(),
            ]);

            return redirect()->route('approvals.business-trips.index')
                ->with('success', 'Permohonan perjalanan dinas berhasil diverifikasi dan akan diteruskan ke HR.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(string $id)
    {
        try {
            $trip = BusinessTrip::findOrFail($id);

            if (Auth::user()->hasRole('Super Admin')) {
                return back()->with('error', 'Super Admin hanya dapat menghapus data, tidak dapat memberikan persetujuan.');
            }



            // Department restriction applies only for manager-scoped users.
            $departmentId = $this->getManagerDepartmentFilter();
            if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui permohonan ini.');
            }

            // Only HR can approve (not manager)
            $user = Auth::user();
            if ($user->hasRole('manager') && !$user->hasRole(['admin', 'hr'])) {
                throw new \Exception('Hanya HR yang dapat menyetujui permohonan perjalanan dinas yang sudah diverifikasi.');
            }

            // Can only approve manager_verified requests
            if ($trip->status !== 'manager_verified') {
                throw new \Exception('Hanya permohonan perjalanan dinas yang sudah diverifikasi oleh manager yang dapat disetujui.');
            }

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

            return redirect()->route('approvals.business-trips.index')->with('success', 'Permohonan perjalanan dinas berhasil disetujui oleh HR.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, string $id)
    {
        try {
            $request->validate(['rejection_reason' => 'required|string|max:1000']);

            $trip = BusinessTrip::findOrFail($id);

            if (Auth::user()->hasRole('Super Admin')) {
                return back()->with('error', 'Super Admin hanya dapat menghapus data, tidak dapat menolak pengajuan.');
            }

            $departmentId = $this->getManagerDepartmentFilter();
            if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menolak permohonan ini.');
            }

            $user = Auth::user();
            $isManager = $user->hasRole('manager') && !$user->hasRole(['admin', 'hr']);
            $requiredStatus = $isManager ? 'pending' : 'manager_verified';

            if ($trip->status !== $requiredStatus) {
                if ($isManager) {
                    throw new \Exception('Hanya permohonan perjalanan dinas yang masih pending yang dapat ditolak oleh manager.');
                }

                throw new \Exception('Hanya permohonan perjalanan dinas yang sudah diverifikasi oleh manager yang dapat ditolak.');
            }

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
                ], $request->rejection_reason, $isManager ? 'Manager' : 'HR');
            }

            return redirect()->route('approvals.business-trips.index')->with('success', $isManager ? 'Permohonan perjalanan dinas berhasil ditolak oleh manager.' : 'Permohonan perjalanan dinas berhasil ditolak oleh HR.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $trip = BusinessTrip::findOrFail($id);

        $departmentId = $this->getManagerDepartmentFilter();
        if ($departmentId && (string) $trip->worker->department_id !== (string) $departmentId) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus permohonan ini.');
        }

        if ($trip->status !== 'cancelled') {
            return back()->with('error', 'Hanya permohonan perjalanan dinas yang sudah dibatalkan yang dapat dihapus.');
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
            'data' => [
                'business_trip_id' => $tripData['id'],
                'type' => 'business_trip',
                'action' => 'approved',
                'title' => 'Perjalanan Dinas Disetujui',
                'message' => sprintf(
                    'Permohonan perjalanan dinas ke %s telah disetujui.',
                    $tripData['destination']
                ),
            ],
        ]);
    }

    private function notifyBusinessTripRejected(string $userId, array $tripData, ?string $reason = null, string $rejectedByLabel = 'HR'): void
    {
        $message = sprintf(
            'Permohonan perjalanan dinas ke %s telah ditolak oleh %s.',
            $tripData['destination'],
            $rejectedByLabel
        );

        if ($reason) {
            $message .= ' Alasan: ' . $reason;
        }

        Notification::create([
            'user_id' => $userId,
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $userId,
            'type' => 'business_trip_rejected',
            'data' => [
                'business_trip_id' => $tripData['id'],
                'type' => 'business_trip',
                'action' => 'rejected',
                'reason' => $reason,
                'title' => 'Perjalanan Dinas Ditolak oleh ' . $rejectedByLabel,
                'message' => $message,
            ],
        ]);
    }
}
