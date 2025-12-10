<?php

namespace App\Services\User;

use App\DTOs\User\UserDTO;
use App\Repositories\Contracts\UserRepositoryInterface;
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

    public function findByWorkerId(string $workerId)
    {
        return $this->repository->findByWorkerId($workerId);
    }

    public function getActiveUsers()
    {
        return $this->repository->getActiveUsers();
    }

    public function create(UserDTO $dto, array $roles = []): array
    {
        try {
            DB::beginTransaction();

            // Check if worker already has user account
            $existingUser = $this->repository->findByWorkerId($dto->workerId);
            if ($existingUser) {
                throw new \Exception('Pegawai ini sudah memiliki akun user');
            }

            $user = $this->repository->create($dto->toArray());

            // Assign roles
            if (!empty($roles)) {
                $this->repository->syncRoles($user->id, $roles);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'User berhasil dibuat',
                'data' => $user,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, UserDTO $dto, ?array $roles = null): array
    {
        try {
            DB::beginTransaction();

            $updated = $this->repository->update($id, $dto->toArray());

            if (!$updated) {
                throw new \Exception('Gagal mengupdate user');
            }

            // Update roles if provided
            if ($roles !== null) {
                $this->repository->syncRoles($id, $roles);
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
                'message' => $e->getMessage(),
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

    public function updatePassword(string $id, string $currentPassword, string $newPassword): array
    {
        try {
            DB::beginTransaction();

            $user = $this->repository->findById($id);

            // Verify current password
            if (!Hash::check($currentPassword, $user->password)) {
                throw new \Exception('Password lama tidak sesuai');
            }

            $updated = $this->repository->updatePassword($id, $newPassword);

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

            $updated = $this->repository->syncRoles($id, $roles);

            if (!$updated) {
                throw new \Exception('Gagal mengupdate role');
            }

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

    public function toggleActive(string $id): array
    {
        try {
            DB::beginTransaction();

            $user = $this->repository->findById($id);

            // Prevent deactivating own account
            if ($user->id === auth()->id()) {
                throw new \Exception('Tidak dapat menonaktifkan akun sendiri');
            }

            $updated = $this->repository->update($id, [
                'is_active' => !$user->is_active,
            ]);

            if (!$updated) {
                throw new \Exception('Gagal mengubah status user');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Status user berhasil diubah',
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
}
