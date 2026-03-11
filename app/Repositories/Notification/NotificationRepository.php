<?php

namespace App\Repositories\Notification;

use App\DTOs\NotificationDTO;
use App\Models\Notification;
use App\Repositories\Contracts\Notification\NotificationRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(
        protected Notification $model
    ) {}

    public function getAll(array $filters = [])
    {
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. Returning empty paginator.');
            $perPage = $filters['per_page'] ?? 15;
            return new LengthAwarePaginator([], 0, $perPage);
        }
        $query = $this->model->query()->with('user')->orderBy('created_at', 'desc');

        if (isset($filters['user_id'])) {
            $query->where('notifiable_type', User::class)
                ->where('notifiable_id', $filters['user_id']);
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
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. getById returning null.');
            return null;
        }
        return $this->model->with('user')->findOrFail($id);
    }

    public function getByUserId(string $userId, array $filters = [])
    {
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. Returning empty paginator for user notifications.');
            $perPage = $filters['per_page'] ?? 15;
            return new LengthAwarePaginator([], 0, $perPage);
        }
        $query = $this->model->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
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
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. getUnreadByUserId returning empty collection.');
            return collect([]);
        }
        return $this->model->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getUnreadCount(string $userId): int
    {
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. getUnreadCount returning 0.');
            return 0;
        }

        return $this->model->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function create(NotificationDTO $dto)
    {
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. Skipping create().');
            return null;
        }

        return $this->model->create($dto->toArray());
    }

    public function update(string $id, NotificationDTO $dto)
    {
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. update() noop.');
            return null;
        }

        $notification = $this->getById($id);
        $notification->update($dto->toArray());
        return $notification->fresh();
    }

    public function delete(string $id): bool
    {
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. delete() noop.');
            return false;
        }

        $notification = $this->getById($id);
        return $notification->delete();
    }

    public function markAsRead(string $id): bool
    {
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. markAsRead() noop.');
            return false;
        }

        $notification = $this->getById($id);
        if (!$notification) {
            Log::warning("Notification {$id} not found when trying to mark as read.");
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    public function markAllAsRead(string $userId): bool
    {
        if (!Schema::hasTable('notifications')) {
            Log::warning('Notifications table does not exist. markAllAsRead() noop.');
            return false;
        }

        return $this->model->query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
