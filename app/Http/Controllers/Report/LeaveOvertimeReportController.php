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

        // Statistics
        $statistics = [
            'total' => LeaveRequest::whereBetween('start_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->count(),
            'approved' => LeaveRequest::where('status', 'approved')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->count(),
            'pending' => LeaveRequest::where('status', 'pending')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->count(),
            'total_days' => LeaveRequest::where('status', 'approved')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->sum('total_days'),
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

        // Statistics
        $statistics = [
            'total' => OvertimeRequest::whereBetween('overtime_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->count(),
            'approved' => OvertimeRequest::where('status', 'approved')
                ->whereBetween('overtime_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->count(),
            'pending' => OvertimeRequest::where('status', 'pending')
                ->whereBetween('overtime_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->count(),
            'rejected' => OvertimeRequest::where('status', 'rejected')
                ->whereBetween('overtime_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->count(),
            'total_hours' => OvertimeRequest::where('status', 'approved')
                ->whereBetween('overtime_date', [$startDate, $endDate])
                ->when($departmentId, function($q) use ($departmentId) {
                    $q->whereHas('worker', fn($sq) => $sq->where('department_id', $departmentId));
                })->sum('total_hours'),
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
