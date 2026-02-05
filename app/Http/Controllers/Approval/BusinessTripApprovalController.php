<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Models\Worker;
use Illuminate\Http\Request;

class BusinessTripApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:business-trip.manage');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        
        $filters = [
            'status' => $request->input('status', ''),
            'worker_id' => $request->input('worker_id'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'per_page' => 15,
        ];

        $query = BusinessTrip::with(['worker.user', 'worker.department']);

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

        // Manager can only see trips from their department, Super Admin sees all
        if (!$isSuperAdmin && $user->hasRole('Manager') && $user->worker) {
            $query->whereHas('worker', function ($q) use ($user) {
                $q->where('department_id', $user->worker->department_id);
            });
        }

        $trips = $query->orderBy('start_date', 'desc')->paginate($filters['per_page']);

        // Calculate statistics
        $statsQuery = BusinessTrip::query();
        if (!$isSuperAdmin && $user->hasRole('Manager') && $user->worker) {
            $statsQuery->whereHas('worker', function ($q) use ($user) {
                $q->where('department_id', $user->worker->department_id);
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
        $workersQuery = Worker::orderBy('name');
        if (!$isSuperAdmin && $user->hasRole('Manager') && $user->worker) {
            $workersQuery->where('department_id', $user->worker->department_id);
        }
        $workers = $workersQuery->get();

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
}
