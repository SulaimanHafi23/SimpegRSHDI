<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Worker;
use App\Models\Department;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveOvertimeReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HR|Manager|Super Admin');
    }

    public function leaveIndex(Request $request)
    {
        $user = auth()->user();

        // Default date range: current year
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfYear()->format('Y-m-d'));
        $departmentId = $request->input('department_id');
        $leaveTypeId = $request->input('leave_type_id');
        $status = $request->input('status');

        $query = LeaveRequest::with(['worker.user', 'worker.department', 'leaveType', 'approvedBy'])
            ->whereBetween('start_date', [$startDate, $endDate]);

        // Manager can only see their department
        if ($user->hasRole('Manager') && $user->worker) {
            $query->whereHas('worker', function($q) use ($user) {
                $q->where('department_id', $user->worker->department_id);
            });
        } elseif ($departmentId) {
            $query->whereHas('worker', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $leaves = $query->orderBy('start_date', 'desc')->paginate(50);

        // Statistics (1 query instead of 5)
        $statsQuery = LeaveRequest::whereBetween('start_date', [$startDate, $endDate])
            ->when($departmentId, function($q) use ($departmentId) {
                $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
            })
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'approved' THEN total_days ELSE 0 END) as total_days
            ")
            ->first();

        $statistics = [
            'total' => $statsQuery->total ?? 0,
            'approved' => $statsQuery->approved ?? 0,
            'pending' => $statsQuery->pending ?? 0,
            'rejected' => $statsQuery->rejected ?? 0,
            'total_days' => $statsQuery->total_days ?? 0,
        ];

        $departments = Department::orderBy('name')->get();
        $leaveTypes = LeaveType::orderBy('name')->get();

        return view('reports.leave.index', compact(
            'leaves',
            'statistics',
            'departments',
            'leaveTypes',
            'startDate',
            'endDate',
            'departmentId',
            'leaveTypeId',
            'status'
        ));
    }

    public function overtimeIndex(Request $request)
    {
        $user = auth()->user();

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->input('department_id');
        $status = $request->input('status');

        $query = OvertimeRequest::with(['worker.user', 'worker.department', 'approvedBy'])
            ->whereBetween('overtime_date', [$startDate, $endDate]);

        // Manager can only see their department
        if ($user->hasRole('Manager') && $user->worker) {
            $query->whereHas('worker', function($q) use ($user) {
                $q->where('department_id', $user->worker->department_id);
            });
        } elseif ($departmentId) {
            $query->whereHas('worker', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $overtimes = $query->orderBy('overtime_date', 'desc')->paginate(50);

        // Statistics (1 query instead of 5)
        $statsQuery = OvertimeRequest::whereBetween('overtime_date', [$startDate, $endDate])
            ->when($departmentId, function($q) use ($departmentId) {
                $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
            })
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'approved' THEN total_hours ELSE 0 END) as total_hours
            ")
            ->first();

        $statistics = [
            'total' => $statsQuery->total ?? 0,
            'approved' => $statsQuery->approved ?? 0,
            'pending' => $statsQuery->pending ?? 0,
            'rejected' => $statsQuery->rejected ?? 0,
            'total_hours' => $statsQuery->total_hours ?? 0,
        ];

        $departments = Department::orderBy('name')->get();

        return view('reports.overtime.index', compact(
            'overtimes',
            'statistics',
            'departments',
            'startDate',
            'endDate',
            'departmentId',
            'status'
        ));
    }
}
