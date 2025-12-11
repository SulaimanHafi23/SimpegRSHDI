<?php

namespace App\Http\Controllers\User;

use App\DTOs\UserDTO;
use App\Services\Role\RoleService;
use App\Services\User\UserService;
use App\Http\Controllers\Controller;
use App\Services\Worker\WorkerService;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\UpdateRolesRequest;
use App\Services\Permission\PermissionService;
use App\Http\Requests\User\UpdatePasswordRequest;
use Illuminate\Http\Request;


class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
        private readonly WorkerService $workerService,
        private readonly RoleService $roleService,
        private readonly PermissionService $permissionService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view-users')->only(['index', 'show']);
        $this->middleware('permission:create-users')->only(['create', 'store']);
        $this->middleware('permission:edit-users')->only(['edit', 'update', 'updatePassword']);
        $this->middleware('permission:delete-users')->only(['destroy']);
        $this->middleware('permission:assign-roles')->only(['updateRoles']);
        $this->middleware('permission:assign-permissions-to-users')->only(['showPermissions', 'updatePermissions']);
        $this->middleware('permission:toggle-user-status')->only(['toggleActive']);
    }

    public function index(Request $request)
    {
        $this->authorizePermission('view-users');

        $filters = [
            'search' => $request->input('search'),
            'is_active' => $request->input('is_active'),
            'role' => $request->input('role'),
        ];

        $users = $this->service->getAllPaginated(15, $filters);
        $roles = $this->roleService->getAll();

        return view('admin.users.index', compact('users', 'roles', 'filters'));
    }

    public function show(string $id)
    {
        $this->authorizePermission('view-users');

        $user = $this->service->findById($id);

        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $this->authorizePermission('create-users');

        $workers = $this->workerService->getActive()
            ->filter(fn($worker) => !$worker->user);
        
        $roles = $this->roleService->getAll();

        return view('admin.users.create', compact('workers', 'roles'));
    }

    public function store(UserRequest $request)
    {
        $this->authorizePermission('create-users');

        $dto = UserDTO::fromRequest($request->validated());
        $roles = $request->input('roles', []);
        
        $result = $this->service->create($dto, $roles);

        if ($result['success']) {
            return redirect()
                ->route('admin.users.show', $result['data']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function edit(string $id)
    {
        $this->authorizePermission('edit-users');

        $user = $this->service->findById($id);
        
        $workers = $this->workerService->getActive()
            ->filter(fn($worker) => !$worker->user || $worker->id === $user->worker_id);
        
        $roles = $this->roleService->getAll();

        return view('admin.users.edit', compact('user', 'workers', 'roles'));
    }

    public function update(UserRequest $request, string $id)
    {
        $this->authorizePermission('edit-users');

        $dto = UserDTO::fromRequest($request->validated());
        $roles = $request->has('roles') ? $request->input('roles') : null;
        
        $result = $this->service->update($id, $dto, $roles);

        if ($result['success']) {
            return redirect()
                ->route('admin.users.show', $id)
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    public function destroy(string $id)
    {
        $this->authorizePermission('delete-users');

        $result = $this->service->delete($id);

        if ($result['success']) {
            return redirect()
                ->route('admin.users.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function updatePassword(UpdatePasswordRequest $request, string $id)
    {
        $this->authorizePermission('edit-users');

        $result = $this->service->updatePassword($id, $request->password);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function updateRoles(UpdateRolesRequest $request, string $id)
    {
        $this->authorizePermission('assign-roles');

        $result = $this->service->updateRoles($id, $request->input('roles', []));

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function showPermissions(string $id)
    {
        $this->authorizePermission('assign-permissions-to-users');

        $user = $this->service->findById($id);
        $permissions = $this->permissionService->getGrouped();
        
        return view('admin.users.permissions', compact('user', 'permissions'));
    }

    public function updatePermissions(Request $request, string $id)
    {
        $this->authorizePermission('assign-permissions-to-users');

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $result = $this->service->updateDirectPermissions($id, $request->input('permissions', []));

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function toggleActive(string $id)
    {
        $this->authorizePermission('toggle-user-status');

        $result = $this->service->toggleActive($id);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
