<?php

namespace App\Services\User;

use App\DTOs\UserDTO;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function findByEmail(string $email)
    {
        return $this->repository->findByEmail($email);
    }

    public function create(UserDTO $dto, array $roles = []): array
    {
        try {
            DB::beginTransaction();

            $data = $dto->toArray();
            $data['password'] = Hash::make($dto->password);

            $user = $this->repository->create($data);

            if (!empty($roles)) {
                $user->syncRoles($roles);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'User berhasil dibuat',
                'data' => $user->load(['worker', 'roles', 'permissions']),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat user: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, UserDTO $dto, ?array $roles = null): array
    {
        try {
            DB::beginTransaction();

            $user = $this->repository->findById($id);

            $data = $dto->toArray();
            
            // Don't update password if not provided
            if (empty($dto->password)) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($dto->password);
            }

            $updated = $this->repository->update($id, $data);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate user');
            }

            // Update roles if provided
            if (!is_null($roles)) {
                $user->syncRoles($roles);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'User berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate user: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $user = $this->repository->findById($id);

            // Prevent deleting own account
            if ($user->id === auth()->id()) {
                throw new \Exception('Tidak dapat menghapus akun sendiri');
            }

            // Prevent deleting super admin
            if ($user->hasRole('Super Admin')) {
                throw new \Exception('Tidak dapat menghapus Super Admin');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus user');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'User berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function updatePassword(string $id, string $password): array
    {
        try {
            DB::beginTransaction();

            $updated = $this->repository->update($id, [
                'password' => Hash::make($password),
            ]);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate password');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Password berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating password: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function updateRoles(string $id, array $roles): array
    {
        try {
            DB::beginTransaction();

            $user = $this->repository->findById($id);

            // Prevent removing Super Admin role from Super Admin
            if ($user->hasRole('Super Admin') && !in_array('Super Admin', $roles)) {
                throw new \Exception('Tidak dapat menghapus role Super Admin');
            }

            $user->syncRoles($roles);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Role berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating roles: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function updateDirectPermissions(string $id, array $permissions): array
    {
        try {
            DB::beginTransaction();

            $user = $this->repository->findById($id);
            
            // Sync direct permissions (not from roles)
            // This will override any previous direct permissions
            $user->syncPermissions($permissions);

            DB::commit();

            Log::info('Direct permissions updated for user: ' . $user->email, [
                'user_id' => $id,
                'permissions' => $permissions,
            ]);

            return [
                'success' => true,
                'message' => 'Direct permissions berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating direct permissions: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function toggleActive(string $id): array
    {
        try {
            DB::beginTransaction();

            $user = $this->repository->findById($id);

            // Prevent disabling own account
            if ($user->id === auth()->id()) {
                throw new \Exception('Tidak dapat menonaktifkan akun sendiri');
            }

            // Prevent disabling Super Admin
            if ($user->hasRole('Super Admin')) {
                throw new \Exception('Tidak dapat menonaktifkan Super Admin');
            }

            $updated = $this->repository->update($id, [
                'is_active' => !$user->is_active,
            ]);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate status user');
            }

            DB::commit();

            $status = $user->is_active ? 'dinonaktifkan' : 'diaktifkan';

            return [
                'success' => true,
                'message' => "User berhasil {$status}",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling user status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getUserPermissions(string $id)
    {
        $user = $this->repository->findById($id);
        
        return [
            'direct_permissions' => $user->permissions,
            'role_permissions' => $user->getPermissionsViaRoles(),
            'all_permissions' => $user->getAllPermissions(),
        ];
    }

    public function getUserRoles(string $id)
    {
        $user = $this->repository->findById($id);
        return $user->roles;
    }

    public function hasPermission(string $id, string $permission): bool
    {
        $user = $this->repository->findById($id);
        return $user->hasPermissionTo($permission);
    }

    public function hasRole(string $id, string $role): bool
    {
        $user = $this->repository->findById($id);
        return $user->hasRole($role);
    }

    public function getActiveUsers()
    {
        return $this->repository->getActive();
    }

    public function getByRole(string $role)
    {
        return $this->repository->getByRole($role);
    }

    public function searchUsers(string $query)
    {
        return $this->repository->search($query);
    }
}
