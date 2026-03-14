<?php

namespace App\Services\User;

use App\DTOs\UserDTO;
use App\Models\Worker;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->userRepository->getAll($filters);
    }

    public function getById(string $id)
    {
        return $this->userRepository->getById($id);
    }

    public function getByUsername(string $username)
    {
        return $this->userRepository->getByUsername($username);
    }

    public function create(array $data)
    {
        // Check if username already exists
        if ($this->userRepository->getByUsername($data['username'])) {
            throw new \Exception('Username already exists.');
        }

        // If worker_id provided, ensure the worker doesn't already have a user
        if (!empty($data['worker_id'])) {
            if ($this->userRepository->getByWorkerId($data['worker_id'])) {
                throw new \Exception('A user is already associated with the selected worker.');
            }
        }

        if (!empty($data['email'])) {
            $workerEmailConflict = Worker::where('email', $data['email'])
                ->when(!empty($data['worker_id']), function ($query) use ($data) {
                    $query->where('id', '!=', $data['worker_id']);
                })
                ->exists();

            if ($workerEmailConflict) {
                throw new \Exception('Email already exists.');
            }
        }

        // Hash password
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $dto = UserDTO::fromRequest($data);
        $user = $this->userRepository->create($dto);

        // Assign roles
        if (!empty($data['roles'])) {
            $this->userRepository->assignRoles($user->id, $data['roles']);
        }

        return $user;
    }

    public function update(string $id, array $data)
    {
        $existingUser = $this->userRepository->getById($id);

        if (!empty($data['email']) && $data['email'] !== $existingUser?->email) {
            $emailOwner = $this->userRepository->getByEmail($data['email']);
            $workerEmailConflict = Worker::where('email', $data['email'])
                ->when($existingUser?->worker_id, function ($query) use ($existingUser) {
                    $query->where('id', '!=', $existingUser->worker_id);
                })
                ->exists();

            if (($emailOwner && $emailOwner->id !== $id) || $workerEmailConflict) {
                throw new \Exception('Email already exists.');
            }
        }

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Remove empty values to prevent overwriting with empty strings
        $data = array_filter($data, function($value) {
            return $value !== '' && $value !== null && $value !== [];
        });

        $dto = UserDTO::fromRequest($data);
        $user = $this->userRepository->update($id, $dto);

        if (!empty($data['email']) && $existingUser?->worker && $existingUser->worker->email !== $user->email) {
            $existingUser->worker->update(['email' => $user->email]);
        }

        // Update roles if provided
        if (isset($data['roles'])) {
            $this->userRepository->syncRoles($user->id, $data['roles']);
        }

        return $user;
    }

    public function delete(string $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function changePassword(string $id, string $currentPassword, string $newPassword)
    {
        $user = $this->userRepository->getById($id);

        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Current password is incorrect.');
        }

        return $this->userRepository->updatePassword($id, Hash::make($newPassword));
    }

    public function resetPassword(string $id, string $newPassword)
    {
        return $this->userRepository->updatePassword($id, Hash::make($newPassword));
    }

    public function activate(string $id)
    {
        return $this->userRepository->activate($id);
    }

    public function deactivate(string $id)
    {
        return $this->userRepository->deactivate($id);
    }

    public function assignRoles(string $id, array $roles)
    {
        return $this->userRepository->assignRoles($id, $roles);
    }

    public function syncRoles(string $id, array $roles)
    {
        return $this->userRepository->syncRoles($id, $roles);
    }
}
