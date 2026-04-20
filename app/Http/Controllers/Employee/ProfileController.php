<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct() {}

    public function show()
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            abort(404, 'Data pegawai tidak ditemukan');
        }

        return view('employee.profile.show', compact('worker', 'user'));
    }

    public function edit()
    {
        $user = Auth::user();
        $worker = $user->worker;

        if (!$worker) {
            abort(404, 'Data pegawai tidak ditemukan');
        }

        return view('employee.profile.edit', compact('worker', 'user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403, 'Pengguna tidak valid');
        }
        $worker = $user->worker;

        if (!$worker) {
            abort(404, 'Data pegawai tidak ditemukan');
        }

        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
                Rule::unique('workers', 'email')->ignore($worker->id),
            ],
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            $user->update([
                'email' => $request->email,
            ]);

            $worker->email = $request->email;

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

        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403, 'Pengguna tidak valid');
        }

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
