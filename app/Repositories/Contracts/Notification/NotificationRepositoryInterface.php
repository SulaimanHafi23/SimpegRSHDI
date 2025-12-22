<?php

namespace App\Repositories\Contracts\Notification;

use App\DTOs\NotificationDTO;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface
{
    public function getAll(array $filters = []);
    public function getById(string $id);
    public function getByUserId(string $userId, array $filters = []);
    public function getUnreadByUserId(string $userId);
    public function getUnreadCount(string $userId): int;
    public function create(NotificationDTO $dto);
    public function update(string $id, NotificationDTO $dto);
    public function delete(string $id): bool;
    public function markAsRead(string $id): bool;
    public function markAllAsRead(string $userId): bool;
}
