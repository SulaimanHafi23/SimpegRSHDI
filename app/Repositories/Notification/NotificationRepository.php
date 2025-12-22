<?php

namespace App\Repositories\Notification;

use App\DTOs\NotificationDTO;
use App\Models\Notification;
use App\Repositories\Contracts\Notification\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(
        protected Notification $model
    ) {}

    public function getAll(array $filters = [])
    {
        $query = $this->model->query()->with('user')->orderBy('created_at', 'desc');

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_read'])) {
            if ($filters['is_read']) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function getById(string $id)
    {
        return $this->model->with('user')->findOrFail($id);
    }

    public function getByUserId(string $userId, array $filters = [])
    {
        $query = $this->model->query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if (isset($filters['is_read'])) {
            if ($filters['is_read']) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    public function getUnreadByUserId(string $userId)
    {
        return $this->model->query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getUnreadCount(string $userId): int
    {
        return $this->model->query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function create(NotificationDTO $dto)
    {
        return $this->model->create($dto->toArray());
    }

    public function update(string $id, NotificationDTO $dto)
    {
        $notification = $this->getById($id);
        $notification->update($dto->toArray());
        return $notification->fresh();
    }

    public function delete(string $id): bool
    {
        $notification = $this->getById($id);
        return $notification->delete();
    }

    public function markAsRead(string $id): bool
    {
        $notification = $this->getById($id);
        $notification->markAsRead();
        return true;
    }

    public function markAllAsRead(string $userId): bool
    {
        return $this->model->query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
