<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    protected $workerService;

    public function __construct(WorkerService $workerService)
    {
        $this->workerService = $workerService;
    }

    public function show()
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            abort(404, 'Data pegawai tidak ditemukan');
        }

        return view('employee.profile.show', compact('worker', 'user'));
    }

    public function edit()
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            abort(404, 'Data pegawai tidak ditemukan');
        }

        return view('employee.profile.edit', compact('worker', 'user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $worker = $user->worker;

        if (!$worker) {
            abort(404, 'Data pegawai tidak ditemukan');
        }

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id . '|unique:workers,email,' . $worker->id,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            // Update user email
            $user->update([
                'email' => $request->email,
            ]);

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($worker->photo_url) {
                    Storage::disk('public')->delete($worker->photo_url);
                }

                $photoPath = $request->file('photo')->store('workers/photos', 'public');
                $worker->photo_url = $photoPath;
            }

            // Update worker fields directly (avoiding DTO for partial update)
            $worker->email = $request->email;
            $worker->phone_number = $request->phone_number;
            $worker->address = $request->address;
            $worker->save();

            return redirect()->route('employee.profile.show')
                ->with('success', 'Profile berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui profile: ' . $e->getMessage());
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai');
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return redirect()->route('employee.profile.show')
                ->with('success', 'Password berhasil diubah');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }
}
