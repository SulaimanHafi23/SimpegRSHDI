<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\WorkerShiftSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $worker = Auth::user()->worker;

        if (!$worker) {
            return redirect()->route('workers.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        // Get month and year from request or use current
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Get schedules for the month
        $schedules = WorkerShiftSchedule::with('shift')
            ->where('worker_id', $worker->id)
            ->whereYear('schedule_date', $year)
            ->whereMonth('schedule_date', $month)
            ->orderBy('schedule_date')
            ->get();

        return view('workers.schedule.index', compact('schedules', 'month', 'year'));
    }
}
