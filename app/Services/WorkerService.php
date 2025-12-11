<?php

namespace App\Services;

use App\DTOs\Worker\WorkerDTO;
use App\Repositories\Contracts\WorkerRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkerService
{
    public function __construct(
        private readonly WorkerRepositoryInterface $repository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function getAllPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getActive()
    {
        return $this->repository->active();
    }

    public function findById(string $id)
    {
        return $this->repository->findById($id);
    }

    public function findByNik(string $nik)
    {
        return $this->repository->findByNik($nik);
    }

    public function create(WorkerDTO $dto): array
    {
        try {
            DB::beginTransaction();

            // Create worker
            $worker = $this->repository->create($dto->toArray());

            // Auto-create user account
            $username = $this->generateUsername($dto->name);
            $defaultPassword = 'password123'; // Should be changed on first login

            $user = $this->userRepository->create([
                'worker_id' => $worker->id,
                'username' => $username,
                'email' => $dto->email,
                'password' => Hash::make($defaultPassword),
                'is_active' => true,
            ]);

            // Assign default role (User)
            $user->assignRole('User');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pegawai berhasil ditambahkan',
                'data' => [
                    'worker' => $worker,
                    'user' => $user,
                    'default_password' => $defaultPassword,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating worker: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menambahkan pegawai: ' . $e->getMessage(),
            ];
        }
    }

    public function update(string $id, WorkerDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $updated = $this->repository->update($id, $dto->toArray());

            if (!$updated) {
                throw new \Exception('Gagal mengupdate data');
            }

            // Update user email if changed
            $worker = $this->repository->findById($id);
            if ($worker->user && $worker->user->email !== $dto->email) {
                $this->userRepository->update($worker->user->id, [
                    'email' => $dto->email,
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data pegawai berhasil diupdate',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating worker: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupdate data pegawai: ' . $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            DB::beginTransaction();

            $worker = $this->repository->findById($id);

            // Check dependencies
            if ($worker->absents()->exists()) {
                throw new \Exception('Pegawai tidak dapat dihapus karena memiliki data absensi');
            }

            if ($worker->leaveRequests()->exists()) {
                throw new \Exception('Pegawai tidak dapat dihapus karena memiliki data cuti');
            }

            if ($worker->overtimes()->exists()) {
                throw new \Exception('Pegawai tidak dapat dihapus karena memiliki data lembur');
            }

            // Delete user account if exists
            if ($worker->user) {
                $this->userRepository->delete($worker->user->id);
            }

            // Delete worker
            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus data');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data pegawai berhasil dihapus',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting worker: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function search(string $keyword, int $perPage = 15)
    {
        return $this->repository->search($keyword, $perPage);
    }

    public function getByPosition(string $positionId)
    {
        return $this->repository->getByPosition($positionId);
    }

    public function getBirthdaysThisMonth()
    {
        return $this->repository->getBirthdaysThisMonth();
    }

    /**
     * Generate unique username from name
     */
    private function generateUsername(string $name): string
    {
        $baseUsername = Str::slug(Str::before($name, ' '), '');
        $username = $baseUsername;
        $counter = 1;

        while ($this->userRepository->findByUsername($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get worker statistics
     */
    public function getStatistics(): array
    {
        $total = $this->repository->all()->count();
        $active = $this->repository->active()->count();
        $permanent = $this->repository->getByStatus('permanent')->count();
        $contract = $this->repository->getByStatus('contract')->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'permanent' => $permanent,
            'contract' => $contract,
            'intern' => $this->repository->getByStatus('intern')->count(),
            'outsourcing' => $this->repository->getByStatus('outsourcing')->count(),
        ];
    }
}
