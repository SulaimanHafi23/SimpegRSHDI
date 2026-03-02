<?php

namespace App\Repositories\Contracts\Attendance;

use App\DTOs\AttendanceDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getById(string $id): ?object;
    public function getByWorkerId(string $workerId, array $filters = []): Collection;
    public function getByDate(string $date, array $filters = []): Collection;
    public function getByWorkerAndDate(string $workerId, string $date): ?object;
    public function create(AttendanceDTO $dto): object;
    public function update(string $id, AttendanceDTO $dto): object;
    public function delete(string $id): bool;
    public function checkIn(AttendanceDTO $dto): object;
    public function checkOut(string $id, AttendanceDTO $dto): object;
    public function getTodayAttendance(string $workerId): ?object;
    public function getMonthlyReport(string $workerId, int $month, int $year): Collection;
    public function getCollectionByPeriod(string $workerId, string $dateFrom, string $dateTo, array $filters = []): Collection;
}
