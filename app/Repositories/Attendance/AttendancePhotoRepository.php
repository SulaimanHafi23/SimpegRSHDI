<?php

namespace App\Repositories\Attendance;

use App\DTOs\AttendancePhotoDTO;
use App\Models\AttendancePhoto;
use App\Repositories\Contracts\Attendance\AttendancePhotoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendancePhotoRepository implements AttendancePhotoRepositoryInterface
{
    public function __construct(
        protected AttendancePhoto $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->with('attendance')->latest('created_at')->get();
    }

    public function getById(string $id): ?object
    {
        return $this->model->with('attendance')->find($id);
    }

    public function getByAttendanceId(string $attendanceId): Collection
    {
        return $this->model->where('attendance_id', $attendanceId)
            ->orderBy('taken_at')
            ->get();
    }

    public function getByType(string $attendanceId, string $type): ?object
    {
        return $this->model->where('attendance_id', $attendanceId)
            ->where('photo_type', $type)
            ->first();
    }

    public function create(AttendancePhotoDTO $dto): object
    {
        $data = $dto->toArray();
        $data['created_at'] = now();
        return $this->model->create($data);
    }

    public function delete(string $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function deleteByAttendanceId(string $attendanceId): bool
    {
        return $this->model->where('attendance_id', $attendanceId)->delete() > 0;
    }
}
