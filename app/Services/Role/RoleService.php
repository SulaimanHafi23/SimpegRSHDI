<?php

namespace App\Services\Role;

use App\DTOs\Role\RoleDTO;
use App\Repositories\Contracts\Role\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function create(RoleDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Check if role already exists
            $existing = $this->repository->findByName($dto->name);
            if ($existing) {
                throw new \Exception('Role dengan nama ini sudah ada');
            }

            $role = $this->repository->create($dto->toArray());

            // Sync permissions
            if (!empty($dto->permissions)) {
                $this->repository->syncPermissions($role->id, $dto->permissions);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Role berhasil dibuat',
                'data' => $role,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating role: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(string $id, RoleDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $role = $this->repository->findById($id);

            // Prevent editing Super Admin role
            if ($role->name === 'Super Admin') {
                throw new \Exception('Role Super Admin tidak dapat diubah');
            }

            $updated = $this->repository->update($id, $dto->toArray());

            if (!$updated) {
                throw new \Exception('Gagal mengupdate role');
            }

            // Sync permissions
            $this->repository->syncPermissions($id, $dto->permissions);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Role berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating role: ' . $e->getMessage());

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

            $role = $this->repository->findById($id);

            // Prevent deleting Super Admin role
            if ($role->name === 'Super Admin') {
                throw new \Exception('Role Super Admin tidak dapat dihapus');
            }

            // Check if role is assigned to users
            if ($role->users()->count() > 0) {
                throw new \Exception('Role masih digunakan oleh ' . $role->users()->count() . ' user');
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus role');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Role berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting role: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function syncPermissions(string $id, array $permissions): array
    {
        try {
            DB::beginTransaction();

            $synced = $this->repository->syncPermissions($id, $permissions);

            if (!$synced) {
                throw new \Exception('Gagal mengupdate permissions');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Permissions berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error syncing permissions: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
