<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use Illuminate\Http\Request;

class BusinessTripApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Manager|HR');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $filters = [
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'worker_search' => $request->input('worker_search'),
            'per_page' => 20,
        ];

        $query = BusinessTrip::with(['worker.user', 'worker.department']);

        // Apply status filter
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Apply date filters
        if ($filters['date_from']) {
            $query->where('start_date', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->where('end_date', '<=', $filters['date_to']);
        }

        // Apply worker search
        if ($filters['worker_search']) {
            $query->whereHas('worker.user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['worker_search'] . '%');
            });
        }

        // Manager can only see trips from their department
        if ($user->hasRole('Manager') && $user->worker) {
            $query->whereHas('worker', function ($q) use ($user) {
                $q->where('department_id', $user->worker->department_id);
            });
        }

        $trips = $query->orderBy('start_date', 'desc')->paginate($filters['per_page']);

        // Calculate statistics
        $statsQuery = BusinessTrip::query();
        if ($user->hasRole('Manager') && $user->worker) {
            $statsQuery->whereHas('worker', function ($q) use ($user) {
                $q->where('department_id', $user->worker->department_id);
            });
        }

        $statistics = [
            'total' => $statsQuery->count(),
            'pending' => $statsQuery->where('status', 'pending')->count(),
            'approved' => $statsQuery->where('status', 'approved')->count(),
            'rejected' => $statsQuery->where('status', 'rejected')->count(),
        ];

        return view('approvals.business-trips.index', compact('trips', 'statistics'));
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
