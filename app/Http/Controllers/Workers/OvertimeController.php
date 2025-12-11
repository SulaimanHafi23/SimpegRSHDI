<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $worker = Auth::user()->worker;

        if (!$worker) {
            return redirect()->route('workers.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        $query = Overtime::where('worker_id', $worker->id);

        // Filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $overtimes = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistics
        $totalHours = Overtime::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereMonth('overtime_date', Carbon::now()->month)
            ->sum('total_hours');

        $pendingOvertimes = Overtime::where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->count();

        return view('workers.overtimes.index', compact('overtimes', 'totalHours', 'pendingOvertimes'));
    }

    public function create()
    {
        return view('workers.overtimes.create');
    }

    public function store(Request $request)
    {
        $worker = Auth::user()->worker;

        if (!$worker) {
            return redirect()->route('workers.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'overtime_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'total_hours' => ['required', 'numeric', 'min:0.5'],
            'description' => ['required', 'string'],
        ]);

        $validated['worker_id'] = $worker->id;
        $validated['status'] = 'pending';

        Overtime::create($validated);

        return redirect()->route('workers.overtimes.index')
            ->with('success', 'Pengajuan lembur berhasil diajukan dan menunggu persetujuan.');
    }

    public function show(string $id)
    {
        $worker = Auth::user()->worker;
        
        $overtime = Overtime::where('id', $id)
            ->where('worker_id', $worker->id)
            ->firstOrFail();

        return view('workers.overtimes.show', compact('overtime'));
    }

    public function edit(string $id)
    {
        $worker = Auth::user()->worker;
        
        $overtime = Overtime::where('id', $id)
            ->where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->firstOrFail();

        return view('workers.overtimes.edit', compact('overtime'));
    }

    public function update(Request $request, string $id)
    {
        $worker = Auth::user()->worker;
        
        $overtime = Overtime::where('id', $id)
            ->where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $validated = $request->validate([
            'overtime_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'total_hours' => ['required', 'numeric', 'min:0.5'],
            'description' => ['required', 'string'],
        ]);

        $overtime->update($validated);

        return redirect()->route('workers.overtimes.show', $id)
            ->with('success', 'Pengajuan lembur berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $worker = Auth::user()->worker;
        
        $overtime = Overtime::where('id', $id)
            ->where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $overtime->delete();

        return redirect()->route('workers.overtimes.index')
            ->with('success', 'Pengajuan lembur berhasil dibatalkan.');
    }
}
