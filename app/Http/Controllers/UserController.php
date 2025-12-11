<?php

namespace App\Http\Controllers;

use App\DTOs\User\UserDTO;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateRolesRequest;
use App\Services\UserService;
use App\Services\WorkerService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
        private readonly WorkerService $workerService
    ) {
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

    public function profile()
    {
        $user = auth()->user()->load(['worker', 'roles']);
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        try {
            $user = auth()->user();
            
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->photo && file_exists(public_path($user->photo))) {
                    unlink(public_path($user->photo));
                }
                
                // Store new photo
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $photo->move(public_path('images/users'), $photoName);
                $validated['photo'] = 'images/users/' . $photoName;
            }

            $user->update($validated);

            return back()->with('success', 'Profile berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui profile: ' . $e->getMessage()]);
        }
    }

    public function updateOwnPassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            auth()->user()->update([
                'password' => bcrypt($validated['password'])
            ]);

            return back()->with('success', 'Password berhasil diubah');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mengubah password: ' . $e->getMessage()]);
        }
    }
}
