<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use App\Services\Worker\WorkerService;
use App\Services\Role\RoleService;
use App\DTOs\UserDTO;
use App\Http\Requests\User\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected WorkerService $workerService,
        protected RoleService $roleService
    ) {
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
        $filters = [
            'search' => $request->search,
            'role' => $request->role,
            'is_active' => $request->is_active,
            'per_page' => $request->per_page ?? 15,
        ];

        $users = $this->userService->getAll($filters);
        $roles = $this->roleService->getAll();

        return view('admin.users.index', compact('users', 'roles', 'filters'));
    }

    public function create()
    {
        $workers = $this->workerService->getAllActive();
        $roles = $this->roleService->getAll();

        return view('admin.users.create', compact('workers', 'roles'));
    }

    public function store(UserRequest $request)
    {
        try {
            $validated = $request->validated();
            // Password will be hashed in the service layer
            // Ensure roles are integers so spatie/laravel-permission treats them as IDs
            if (!empty($validated['roles']) && is_array($validated['roles'])) {
                $validated['roles'] = array_map(fn($r) => is_numeric($r) ? (int) $r : $r, $validated['roles']);
            }
            $user = $this->userService->create($validated);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $user = $this->userService->getById($id);
            return view('admin.users.show', compact('user'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $user = $this->userService->getById($id);
            $workers = $this->workerService->getAllActive();
            $roles = $this->roleService->getAll();

            return view('admin.users.edit', compact('user', 'workers', 'roles'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(UserRequest $request, string $id)
    {
        try {
            $validated = $request->validated();
            // Password will be hashed in the service layer; remove if empty
            if (!empty($validated['password'])) {
                // keep as-is, service will hash
            } else {
                unset($validated['password']);
            }

            // Ensure roles are integers so spatie/laravel-permission treats them as IDs
            if (isset($validated['roles']) && is_array($validated['roles'])) {
                $validated['roles'] = array_map(fn($r) => is_numeric($r) ? (int) $r : $r, $validated['roles']);
            }

            $user = $this->userService->update($id, $validated);

            return redirect()
                ->route('admin.users.show', $id)
                ->with('success', 'User berhasil diupdate');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->userService->delete($id);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
