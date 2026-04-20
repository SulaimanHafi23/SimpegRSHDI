<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Enforce permissions by middleware to match RolePermissionSeeder
        $this->middleware('permission:user.manage')->only(['index', 'show']);
        $this->middleware('permission:user.manage')->only(['create', 'store']);
        $this->middleware('permission:user.manage')->only(['edit', 'update']);
        $this->middleware('permission:user.manage')->only(['destroy']);
        // Only allow role assignment to authorized users
        $this->middleware('permission:user.manage')->only(['store', 'update']);
    }

    public function index(Request $request)
    {
        $query = User::with(['worker', 'roles']);

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($roleQuery) use ($request) {
                $roleQuery->where('name', $request->role);
            });
        }

        if ($request->filled('search')) {
            $searchTerm = strtolower($request->search);
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->whereRaw('LOWER(username) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereHas('worker', function ($workerQuery) use ($searchTerm) {
                        $workerQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
                            ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . $searchTerm . '%']);
                    });
            });
        }

        $filters = [
            'search' => $request->search,
            'role' => $request->role,
            'is_active' => $request->is_active,
            'per_page' => $request->per_page ?? 15,
        ];

        $users = $query->latest()->paginate($filters['per_page'])->appends($filters);
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'filters'));
    }

    public function create()
    {
        $workers = Worker::where('status', 'active')->orderBy('name')->get();
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('admin.users.create', compact('workers', 'roles'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'worker_id' => ['required', 'uuid', Rule::exists('workers', 'id')],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
                'is_active' => 'boolean',
                'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
                'roles' => 'nullable|array',
                'roles.*' => 'integer|exists:roles,id',
            ]);

            if (!empty($validated['roles']) && is_array($validated['roles'])) {
                $validated['roles'] = array_map(fn ($roleId) => is_numeric($roleId) ? (int) $roleId : $roleId, $validated['roles']);
            }

            DB::beginTransaction();

            if (User::where('username', $validated['username'])->exists()) {
                throw new \Exception('Username already exists.');
            }

            if (Worker::where('id', $validated['worker_id'])->exists() && User::where('worker_id', $validated['worker_id'])->exists()) {
                throw new \Exception('A user is already associated with the selected worker.');
            }

            if (!empty($validated['email'])) {
                $workerEmailConflict = Worker::where('email', $validated['email'])
                    ->where('id', '!=', $validated['worker_id'])
                    ->exists();

                if ($workerEmailConflict) {
                    throw new \Exception('Email already exists.');
                }
            }

            $user = User::create([
                'worker_id' => $validated['worker_id'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'is_active' => $request->boolean('is_active'),
            ]);

            if (!empty($validated['roles'])) {
                $user->assignRole($validated['roles']);
            }

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $user = User::with(['worker', 'roles.permissions'])->findOrFail($id);
            return view('admin.users.show', compact('user'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'User tidak ditemukan');
        }
    }

    public function edit(string $id)
    {
        try {
            $user = User::with(['worker', 'roles.permissions'])->findOrFail($id);
            $workers = Worker::where('status', 'active')->orderBy('name')->get();
            $roles = Role::with('permissions')->orderBy('name')->get();

            return view('admin.users.edit', compact('user', 'workers', 'roles'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'User tidak ditemukan');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'worker_id' => ['nullable', 'uuid', Rule::exists('workers', 'id')],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
                'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($id)],
                'password' => 'nullable|string|min:8|confirmed',
                'password_confirmation' => 'nullable|string|min:8',
                'is_active' => 'boolean',
                'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
                'roles' => 'nullable|array',
                'roles.*' => 'integer|exists:roles,id',
            ]);

            if (isset($validated['roles']) && is_array($validated['roles'])) {
                $validated['roles'] = array_map(fn ($roleId) => is_numeric($roleId) ? (int) $roleId : $roleId, $validated['roles']);
            }

            DB::beginTransaction();

            $existingUser = User::with('worker')->findOrFail($id);

            if (!empty($validated['email']) && $validated['email'] !== $existingUser->email) {
                $emailOwner = User::where('email', $validated['email'])->first();
                $workerEmailConflict = Worker::where('email', $validated['email'])
                    ->when($existingUser->worker_id, function ($query) use ($existingUser) {
                        $query->where('id', '!=', $existingUser->worker_id);
                    })
                    ->exists();

                if (($emailOwner && $emailOwner->id !== $id) || $workerEmailConflict) {
                    throw new \Exception('Email already exists.');
                }
            }

            $payload = [
                'worker_id' => $validated['worker_id'] ?? null,
                'email' => $validated['email'],
                'username' => $validated['username'],
                'is_active' => $request->boolean('is_active'),
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }

            $payload = array_filter($payload, function ($value) {
                return $value !== '' && $value !== null && $value !== [];
            });

            $user = User::findOrFail($id);
            $user->update($payload);

            if (!empty($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            DB::commit();

            return redirect()
                ->route('admin.users.show', $id)
                ->with('success', 'User berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            User::findOrFail($id)->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
