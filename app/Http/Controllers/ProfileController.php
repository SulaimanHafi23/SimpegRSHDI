<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = Auth::user();
        $worker = null;

        if ($user && $user->worker_id) {
            $worker = Worker::find($user->worker_id);
        }

        return view('admin.profile', compact('user', 'worker'));
    }

    public function edit()
    {
        $user = Auth::user();
        $worker = null;

        if ($user && $user->worker_id) {
            $worker = Worker::find($user->worker_id);
        }

        return view('profile.edit', compact('user', 'worker'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return back()->withErrors(['email' => 'User tidak ditemukan.'])->withInput();
        }

        // Prepare user data for update
        $userData = [
            'email' => $request->email,
            // Don't update password, username, roles, etc. in profile update
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // If the user is linked to a worker, update the worker's photo instead
            if ($user->worker_id) {
                try {
                    $this->updateWorkerProfile($user->worker_id, [
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
                        $imageClass = '\\Intervention\\Image\\ImageManagerStatic';
                        if (class_exists($imageClass)) {
                            $img = $imageClass::make($file->getRealPath());
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
            $this->updateUserProfile($user, $userData);

            if ($user->worker_id && ($request->has('name') || $request->has('phone') || $request->has('address'))) {
                $workerData = array_filter([
                    'name' => $request->name,
                    'phone_number' => $request->phone ?? $request->phone_number ?? null,
                    'address' => $request->address,
                ]);

                if (!empty($workerData)) {
                    $this->updateWorkerProfile($user->worker_id, $workerData);
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
        $user = Auth::user();
        if (!$user) {
            return back()->withErrors(['current_password' => 'User tidak ditemukan.']);
        }

        try {
            if (!Hash::check($request->current_password, (string) $user->password)) {
                throw new \Exception('Password lama yang Anda masukkan tidak sesuai.');
            }

            $user->update([
                'password' => Hash::make((string) $request->password),
            ]);

            return redirect()
                ->route('profile.show')
                ->with('success', 'Password berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors([
                'current_password' => $e->getMessage()
            ]);
        }
    }

    private function updateUserProfile(User $user, array $data): User
    {
        if (!empty($data['email']) && $data['email'] !== $user->email) {
            $emailOwner = User::where('email', $data['email'])->first();
            $workerEmailConflict = Worker::where('email', $data['email'])
                ->when($user->worker_id, function ($query) use ($user) {
                    $query->where('id', '!=', $user->worker_id);
                })
                ->exists();

            if (($emailOwner && $emailOwner->id !== $user->id) || $workerEmailConflict) {
                throw new \Exception('Email sudah digunakan oleh pengguna lain.');
            }
        }

        $data = array_filter($data, function ($value) {
            return $value !== '' && $value !== null && $value !== [];
        });

        $user->update($data);
        $user = $user->fresh(['worker']);

        if (!empty($data['email']) && $user->worker && $user->worker->email !== $user->email) {
            $user->worker->update(['email' => $user->email]);
        }

        return $user;
    }

    private function updateWorkerProfile(string $workerId, array $data): Worker
    {
        $worker = Worker::with('user')->findOrFail($workerId);

        if (!empty($data['email']) && $data['email'] !== $worker->email) {
            $existingWorker = Worker::where('email', $data['email'])->first();
            $existingUser = User::where('email', $data['email'])
                ->where('worker_id', '!=', $worker->id)
                ->first();

            if (($existingWorker && $existingWorker->id !== $worker->id) || $existingUser) {
                throw new \Exception('Email sudah digunakan oleh pengguna lain.');
            }
        }

        if (isset($data['photo'])) {
            if ($worker->photo_url && Storage::exists($worker->photo_url)) {
                Storage::delete($worker->photo_url);
            }
            $data['photo_url'] = $this->saveWorkerPhoto($data['photo'], (string) $worker->nip);
            unset($data['photo']);
        }

        $data = array_filter($data, function ($value) {
            return $value !== '' && $value !== null && $value !== [];
        });

        $worker->update($data);
        $worker = $worker->fresh(['user']);

        if (!empty($data['email']) && $worker->user && $worker->user->email !== $worker->email) {
            $worker->user->update(['email' => $worker->email]);
        }

        return $worker;
    }

    private function saveWorkerPhoto($photo, string $nip): string
    {
        $ext = strtolower($photo->getClientOriginalExtension() ?? 'jpg');
        $filename = sprintf('%s_photo_%s.%s', $nip, now()->format('YmdHis'), $ext);

        try {
            $imageClass = '\\Intervention\\Image\\ImageManagerStatic';
            if (class_exists($imageClass)) {
                $img = $imageClass::make($photo->getRealPath());
                $img->orientate();

                if ($img->width() > 1200) {
                    $img->resize(1200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $encoded = (string) $img->encode($ext, 75);
                $path = 'worker-photos/' . $filename;
                Storage::disk('public')->put($path, $encoded);

                return $path;
            }
        } catch (\Throwable $e) {
            // Fallback below.
        }

        return $photo->storeAs('worker-photos', $filename, 'public');
    }
}
