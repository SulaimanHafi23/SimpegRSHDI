<?php

namespace App\Repositories\Contracts\Attendance;

use App\DTOs\AttendancePhotoDTO;
use Illuminate\Database\Eloquent\Collection;

interface AttendancePhotoRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(string $id): ?object;
    public function getByAttendanceId(string $attendanceId): Collection;
    public function getByType(string $attendanceId, string $type): ?object;
    public function create(AttendancePhotoDTO $dto): object;
    public function delete(string $id): bool;
    public function deleteByAttendanceId(string $attendanceId): bool;
}
