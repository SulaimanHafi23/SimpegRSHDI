<?php

namespace App\Services\Worker;

use App\DTOs\WorkerDTO;
use App\DTOs\UserDTO;
use App\Repositories\Contracts\Worker\WorkerRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkerService
{
    public function __construct(
        protected WorkerRepositoryInterface $workerRepository,
        protected UserRepositoryInterface $userRepository,
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->workerRepository->getAll($filters);
    }

    public function getAllActive()
    {
        return $this->workerRepository->getAllActive();
    }

    public function getById(string $id)
    {
        return $this->workerRepository->getById($id);
    }

    /**
     * Alias for getById() for backward compatibility
     */
    public function findById(string $id)
    {
        return $this->getById($id);
    }

    public function getByNip(string $nip)
    {
        return $this->workerRepository->getByNip($nip);
    }

    public function getByDepartment(string $departmentId)
    {
        return $this->workerRepository->getByDepartment($departmentId);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            // Check if NIP already exists
            if ($this->workerRepository->getByNip($data['nip'])) {
                throw new \Exception('NIP already exists.');
            }

            // Check if email already exists
            if ($this->workerRepository->getByEmail($data['email'])) {
                throw new \Exception('Email already exists.');
            }

            // Handle photo upload
            if (isset($data['photo'])) {
                $data['photo_url'] = $this->savePhoto($data['photo'], $data['nip']);
            }

            $workerDTO = WorkerDTO::fromRequest($data);
            $worker = $this->workerRepository->create($workerDTO);

            // Create user account if requested
            if ($data['create_user_account'] ?? false) {
                $userDTO = UserDTO::fromRequest([
                    'worker_id' => $worker->id,
                    'email' => $worker->email,
                    'username' => $data['username'] ?? $worker->nip,
                    'password' => $data['password'] ?? 'password123',
                    'is_active' => true,
                ]);

                $user = $this->userRepository->create($userDTO);

                // Assign default role
                if (isset($data['roles'])) {
                    $this->userRepository->assignRoles($user->id, $data['roles']);
                }
            }

            DB::commit();
            return $this->workerRepository->getById($worker->id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $worker = $this->workerRepository->getById($id);

            // Handle photo upload
            if (isset($data['photo'])) {
                // Delete old photo
                if ($worker->photo_url && Storage::exists($worker->photo_url)) {
                    Storage::delete($worker->photo_url);
                }
                $data['photo_url'] = $this->savePhoto($data['photo'], $worker->nip);
            }

            $dto = WorkerDTO::fromRequest($data);
            $updated = $this->workerRepository->update($id, $dto);

            DB::commit();
            return $updated;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        DB::beginTransaction();
        try {
            $worker = $this->workerRepository->getById($id);

            // Delete photo
            if ($worker->photo_url && Storage::exists($worker->photo_url)) {
                Storage::delete($worker->photo_url);
            }

            // Delete user account
            if ($worker->user) {
                $this->userRepository->delete($worker->user->id);
            }

            $result = $this->workerRepository->delete($id);

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function resign(string $id, string $resignDate)
    {
        DB::beginTransaction();
        try {
            $worker = $this->workerRepository->resign($id, $resignDate);

            // Deactivate user account
            if ($worker->user) {
                $this->userRepository->deactivate($worker->user->id);
            }

            DB::commit();
            return $worker;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function savePhoto($photo, string $nip): string
    {
        $filename = sprintf(
            '%s_photo_%s.%s',
            $nip,
            now()->format('YmdHis'),
            $photo->getClientOriginalExtension()
        );

        return $photo->storeAs('worker-photos', $filename, 'public');
    }
}