<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Services\User\UserService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly WorkerService $workerService
    ) {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = auth()->user();
        $worker = null;

        if ($user->worker_id) {
            $worker = $this->workerService->findById($user->worker_id);
        }

        return view('admin.profile', compact('user', 'worker'));
    }

    public function edit()
    {
        $user = auth()->user();
        $worker = null;

        if ($user->worker_id) {
            $worker = $this->workerService->findById($user->worker_id);
        }

        return view('profile.edit', compact('user', 'worker'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        
        // Prepare photo path
        $photoPath = $user->photo;
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $photoPath = $request->file('photo')->store('profile-photos', 'public');
        }

        // Create DTO with all required fields
        $dto = new UserDTO(
            id: $user->id,
            workerId: $user->worker_id,
            email: $request->email,
            password: null, // Don't update password here
            emailVerifiedAt: $user->email_verified_at?->toDateTimeString(),
            isActive: $user->is_active,
            photo: $photoPath,
        );

        // Update user
        $this->userService->update($user->id, $dto);

        // If user has worker data, update worker info
        if ($user->worker_id && ($request->has('name') || $request->has('phone'))) {
            $workerData = array_filter([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            if (!empty($workerData)) {
                $this->workerService->updateProfile($user->worker_id, $workerData);
            }
        }

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profile berhasil diperbarui');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai'
            ]);
        }

        $this->userService->updatePassword($user->id, $request->new_password);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Password berhasil diperbarui');
    }
}
