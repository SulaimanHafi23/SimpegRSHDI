<?php

namespace App\Services\Attendance;

use App\DTOs\AttendanceDTO;
use App\DTOs\AttendancePhotoDTO;
use App\Repositories\Contracts\Attendance\AttendanceRepositoryInterface;
use App\Repositories\Contracts\Attendance\AttendancePhotoRepositoryInterface;
use App\Repositories\Contracts\WorkerShift\WorkerShiftRepositoryInterface;
use App\Repositories\Contracts\Master\LocationRepositoryInterface;
use App\Repositories\Contracts\Master\ShiftRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttendanceService
{
    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected AttendancePhotoRepositoryInterface $attendancePhotoRepository,
        protected WorkerShiftRepositoryInterface $workerShiftRepository,
        protected LocationRepositoryInterface $locationRepository,
        protected ShiftRepositoryInterface $shiftRepository,
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->attendanceRepository->getAll($filters);
    }

    public function getById(string $id)
    {
        return $this->attendanceRepository->getById($id);
    }

    public function getByWorkerId(string $workerId, array $filters = [])
    {
        return $this->attendanceRepository->getByWorkerId($workerId, $filters);
    }

    public function getTodayAttendance(string $workerId)
    {
        return $this->attendanceRepository->getTodayAttendance($workerId);
    }

    public function getMonthlyReport(string $workerId, int $month, int $year)
    {
        return $this->attendanceRepository->getMonthlyReport($workerId, $month, $year);
    }

    public function checkIn(array $data)
    {
        DB::beginTransaction();
        try {
            $workerId = $data['worker_id'];
            $today = now()->format('Y-m-d');

            // Check if already checked in today
            $existing = $this->attendanceRepository->getByWorkerAndDate($workerId, $today);
            if ($existing) {
                throw new \Exception('You have already checked in today.');
            }

            // Get worker's shift for today
            $workerShift = $this->workerShiftRepository->getActiveByWorkerId($workerId);
            if (!$workerShift) {
                throw new \Exception('No active shift assigned to this worker.');
            }

            $shift = $this->shiftRepository->getById($workerShift->shift_id);
            
            // Validate location
            $location = $this->locationRepository->getById($data['location_id']);
            $distance = $location->calculateDistance($data['latitude'], $data['longitude']);
            $isOutsideRadius = $distance > $location->radius;

            // Calculate if late
            $checkInTime = now();
            $shiftStartTime = \Carbon\Carbon::parse($shift->start_time);
            $graceTime = $shiftStartTime->copy()->addMinutes($shift->grace_period_minutes);
            
            $isLate = $checkInTime->greaterThan($graceTime);
            $lateMinutes = $isLate ? $checkInTime->diffInMinutes($shiftStartTime) : 0;

            // Create attendance
            $attendanceDTO = AttendanceDTO::fromRequest([
                'worker_id' => $workerId,
                'shift_id' => $shift->id,
                'location_id' => $location->id,
                'attendance_date' => $today,
                'check_in' => $checkInTime,
                'check_in_latitude' => $data['latitude'],
                'check_in_longitude' => $data['longitude'],
                'distance_check_in' => $distance,
                'status' => 'present',
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'is_outside_radius' => $isOutsideRadius,
            ]);

            $attendance = $this->attendanceRepository->create($attendanceDTO);

            // Save photo if provided
            if (isset($data['photo'])) {
                $photoPath = $this->savePhoto($data['photo'], 'check_in', $workerId);
                
                $photoDTO = AttendancePhotoDTO::fromRequest([
                    'attendance_id' => $attendance->id,
                    'photo_path' => $photoPath,
                    'photo_type' => 'check_in',
                    'taken_at' => $checkInTime,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);

                $this->attendancePhotoRepository->create($photoDTO);
            }

            DB::commit();
            return $this->attendanceRepository->getById($attendance->id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function checkOut(string $attendanceId, array $data)
    {
        DB::beginTransaction();
        try {
            $attendance = $this->attendanceRepository->getById($attendanceId);
            
            if (!$attendance) {
                throw new \Exception('Attendance record not found.');
            }

            if ($attendance->check_out) {
                throw new \Exception('You have already checked out.');
            }

            // Validate location
            $location = $this->locationRepository->getById($data['location_id']);
            $distance = $location->calculateDistance($data['latitude'], $data['longitude']);

            // Calculate early leave
            $checkOutTime = now();
            $shift = $this->shiftRepository->getById($attendance->shift_id);
            $shiftEndTime = \Carbon\Carbon::parse($shift->end_time);
            
            // Handle overnight shifts
            if ($shift->is_overnight && $checkOutTime->lt($shiftEndTime)) {
                $shiftEndTime->subDay();
            }

            $isEarlyLeave = $checkOutTime->lessThan($shiftEndTime);
            $earlyLeaveMinutes = $isEarlyLeave ? $checkOutTime->diffInMinutes($shiftEndTime) : 0;

            // Calculate overtime
            $overtimeMinutes = $checkOutTime->greaterThan($shiftEndTime) 
                ? $checkOutTime->diffInMinutes($shiftEndTime) 
                : 0;

            // Update attendance
            $attendanceDTO = AttendanceDTO::fromRequest(array_merge(
                $attendance->toArray(),
                [
                    'check_out' => $checkOutTime,
                    'check_out_latitude' => $data['latitude'],
                    'check_out_longitude' => $data['longitude'],
                    'distance_check_out' => $distance,
                    'is_early_leave' => $isEarlyLeave,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                ]
            ));

            $this->attendanceRepository->update($attendanceId, $attendanceDTO);

            // Save photo if provided
            if (isset($data['photo'])) {
                $photoPath = $this->savePhoto($data['photo'], 'check_out', $attendance->worker_id);
                
                $photoDTO = AttendancePhotoDTO::fromRequest([
                    'attendance_id' => $attendanceId,
                    'photo_path' => $photoPath,
                    'photo_type' => 'check_out',
                    'taken_at' => $checkOutTime,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);

                $this->attendancePhotoRepository->create($photoDTO);
            }

            DB::commit();
            return $this->attendanceRepository->getById($attendanceId);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data)
    {
        $dto = AttendanceDTO::fromRequest($data);
        return $this->attendanceRepository->update($id, $dto);
    }

    public function delete(string $id): bool
    {
        $attendance = $this->attendanceRepository->getById($id);
        
        // Delete photos
        foreach ($attendance->photos as $photo) {
            if (Storage::exists($photo->photo_path)) {
                Storage::delete($photo->photo_path);
            }
            $this->attendancePhotoRepository->delete($photo->id);
        }

        return $this->attendanceRepository->delete($id);
    }

    protected function savePhoto($photo, string $type, string $workerId): string
    {
        $filename = sprintf(
            '%s_%s_%s.%s',
            $workerId,
            $type,
            now()->format('YmdHis'),
            $photo->getClientOriginalExtension()
        );

        return $photo->storeAs('attendance-photos', $filename, 'public');
    }
}