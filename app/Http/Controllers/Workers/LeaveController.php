<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $worker = Auth::user()->worker;

        if (!$worker) {
            return redirect()->route('workers.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        $query = LeaveRequest::where('worker_id', $worker->id);

        // Filters
        if ($request->filled('search')) {
            $query->where('leave_type', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate statistics
        $usedLeaves = LeaveRequest::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('total_days');

        $leaveBalance = 12 - $usedLeaves;

        $pendingLeaves = LeaveRequest::where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->count();

        return view('workers.leaves.index', compact('leaves', 'leaveBalance', 'usedLeaves', 'pendingLeaves'));
    }

    public function create()
    {
        return view('workers.leaves.create');
    }

    public function store(Request $request)
    {
        $worker = Auth::user()->worker;

        if (!$worker) {
            return redirect()->route('workers.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan.');
        }

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'total_days' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['worker_id'] = $worker->id;
        $validated['status'] = 'pending';

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/leaves'), $filename);
            $validated['attachment'] = 'uploads/leaves/' . $filename;
        }

        LeaveRequest::create($validated);

        return redirect()->route('workers.leaves.index')
            ->with('success', 'Pengajuan cuti berhasil diajukan dan menunggu persetujuan.');
    }

    public function show(string $id)
    {
        $worker = Auth::user()->worker;
        
        $leave = LeaveRequest::where('id', $id)
            ->where('worker_id', $worker->id)
            ->firstOrFail();

        $usedLeaves = LeaveRequest::where('worker_id', $worker->id)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('total_days');

        $leaveBalance = 12 - $usedLeaves;

        return view('workers.leaves.show', compact('leave', 'leaveBalance', 'usedLeaves'));
    }

    public function edit(string $id)
    {
        $worker = Auth::user()->worker;
        
        $leave = LeaveRequest::where('id', $id)
            ->where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->firstOrFail();

        return view('workers.leaves.edit', compact('leave'));
    }

    public function update(Request $request, string $id)
    {
        $worker = Auth::user()->worker;
        
        $leave = LeaveRequest::where('id', $id)
            ->where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'total_days' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file
            if ($leave->attachment && file_exists(public_path($leave->attachment))) {
                unlink(public_path($leave->attachment));
            }

            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/leaves'), $filename);
            $validated['attachment'] = 'uploads/leaves/' . $filename;
        }

        $leave->update($validated);

        return redirect()->route('workers.leaves.show', $id)
            ->with('success', 'Pengajuan cuti berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $worker = Auth::user()->worker;
        
        $leave = LeaveRequest::where('id', $id)
            ->where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Delete attachment if exists
        if ($leave->attachment && file_exists(public_path($leave->attachment))) {
            unlink(public_path($leave->attachment));
        }

        $leave->delete();

        return redirect()->route('workers.leaves.index')
            ->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }

    public function download(string $id)
    {
        $worker = Auth::user()->worker;
        
        $leave = LeaveRequest::where('id', $id)
            ->where('worker_id', $worker->id)
            ->firstOrFail();

        if (!$leave->attachment || !file_exists(public_path($leave->attachment))) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        return response()->download(public_path($leave->attachment));
    }
}
