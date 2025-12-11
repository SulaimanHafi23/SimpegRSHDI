<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\Absent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $worker = Auth::user()->worker;

        if (!$worker) {
            return redirect()->route('workers.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        // Get today's attendance
        $todayAttendance = Absent::where('worker_id', $worker->id)
            ->whereDate('check_in', Carbon::today())
            ->first();

        // Get this month statistics
        $monthlyStats = Absent::where('worker_id', $worker->id)
            ->whereMonth('check_in', Carbon::now()->month)
            ->whereYear('check_in', Carbon::now()->year)
            ->count();

        return view('workers.attendance.index', compact('todayAttendance', 'monthlyStats'));
    }

    public function history(Request $request)
    {
        $worker = Auth::user()->worker;

        if (!$worker) {
            return redirect()->route('workers.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        $query = Absent::where('worker_id', $worker->id);

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('check_in', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('check_in', '<=', $request->end_date);
        }

        $attendances = $query->orderBy('check_in', 'desc')->paginate(15);

        return view('workers.attendance.history', compact('attendances'));
    }
}
