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
        // Permission check dilakukan di blade dengan @can
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
            $validated['password'] = Hash::make($validated['password']);
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
            
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
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
