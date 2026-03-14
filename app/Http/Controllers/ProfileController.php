<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Services\User\UserService;
use App\Services\Worker\WorkerService;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

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
        // Prepare user data for update
        $userData = [
            'email' => $request->email,
            // Don't update password, username, roles, etc. in profile update
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // If the user is linked to a worker, update the worker's photo instead
            if ($user->worker_id) {
                // Delegate to WorkerService which handles deleting old photo and saving new one
                try {
                    $this->workerService->update($user->worker_id, [
                        'photo' => $request->file('photo'),
                    ]);
                } catch (\Exception $e) {
                    return back()->withErrors(['photo' => 'Gagal menyimpan foto pegawai: ' . $e->getMessage()])->withInput();
                }
            } else {
                // Delete old user photo (public disk)
                try {
                    if ($user->photo) {
                        Storage::disk('public')->delete($user->photo);
                    }

                    $file = $request->file('photo');
                    $ext = strtolower($file->getClientOriginalExtension() ?? 'jpg');
                    $filename = sprintf('profile_%s.%s', now()->format('YmdHis'), $ext);

                    try {
                        if (class_exists('\\Intervention\\Image\\ImageManagerStatic')) {
                            $img = Image::make($file->getRealPath());
                            $img->orientate();
                            if ($img->width() > 1200) {
                                $img->resize(1200, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                    $constraint->upsize();
                                });
                            }

                            $encoded = (string) $img->encode($ext, 75);
                            $path = 'profile-photos/' . $filename;
                            Storage::disk('public')->put($path, $encoded);
                            $userData['photo'] = $path;
                        } else {
                            // fallback to storing original file
                            $path = $file->storeAs('profile-photos', $filename, 'public');
                            $userData['photo'] = $path;
                        }
                    } catch (\Throwable $e) {
                        // fallback
                        $path = $file->storeAs('profile-photos', $filename, 'public');
                        $userData['photo'] = $path;
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(['photo' => 'Gagal menyimpan foto profil: ' . $e->getMessage()])->withInput();
                }
            }
        }

        try {
            $this->userService->update($user->id, $userData);

            if ($user->worker_id && ($request->has('name') || $request->has('phone') || $request->has('address'))) {
                $workerData = array_filter([
                    'name' => $request->name,
                    'phone_number' => $request->phone ?? $request->phone_number ?? null,
                    'address' => $request->address,
                ]);

                if (!empty($workerData)) {
                    $this->workerService->update($user->worker_id, $workerData);
                }
            }
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profile berhasil diperbarui');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();

        try {
            // Use changePassword method which verifies current password internally
            // The UpdatePasswordRequest validates 'password' (confirmed) so use that field
            $this->userService->changePassword(
                $user->id,
                $request->current_password,
                $request->password
            );

            return redirect()
                ->route('profile.show')
                ->with('success', 'Password berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors([
                'current_password' => $e->getMessage()
            ]);
        }
    }
}
