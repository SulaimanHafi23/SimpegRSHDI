<?php

namespace App\Http\Controllers\Admin\User;

use App\DTOs\User\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateRolesRequest;
use App\Services\User\UserService;
use App\Services\Worker\WorkerService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
        private readonly WorkerService $workerService
    ) {
        $this->middleware(['auth']);
        // $this->middleware(['permission:manage-users']); // Uncomment if using permissions
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'is_active' => $request->input('is_active'),
            'role' => $request->input('role'),
        ];

        $users = $this->service->getAllPaginated(15, $filters);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles', 'filters'));
    }

    public function show(string $id)
    {
        $user = $this->service->findById($id);
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        // Get workers that don't have user accounts
        $workers = $this->workerService->getActive()
            ->filter(function ($worker) {
                return !$worker->user;
            });
        
        $roles = Role::all();

        return view('admin.users.create', compact('workers', 'roles'));
    }

    public function store(UserRequest $request)
    {
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
        $user = $this->service->findById($id);
        
        // Get workers that don't have user accounts (exclude current user's worker)
        $workers = $this->workerService->getActive()
            ->filter(function ($worker) use ($user) {
                return !$worker->user || $worker->id === $user->worker_id;
            });
        
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'workers', 'roles'));
    }

    public function update(UserRequest $request, string $id)
    {
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
        $result = $this->service->updatePassword(
            $id,
            $request->input('current_password'),
            $request->input('password')
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function updateRoles(UpdateRolesRequest $request, string $id)
    {
        $result = $this->service->updateRoles($id, $request->input('roles'));

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function toggleActive(string $id)
    {
        $result = $this->service->toggleActive($id);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
