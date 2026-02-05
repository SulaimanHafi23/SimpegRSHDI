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
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic as Image;

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
                throw new \Exception('Anda sudah melakukan check-in hari ini.');
            }

            // Check if today is a national holiday
            $holiday = \App\Models\Holiday::where('is_national', true)
                ->whereDate('date', $today)
                ->first();
            if ($holiday) {
                throw new \Exception('Hari ini adalah libur nasional (' . $holiday->name . '). Anda tidak perlu melakukan absensi.');
            }

            // Check if worker is on approved leave today
            $approvedLeave = \App\Models\LeaveRequest::where('worker_id', $workerId)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
            if ($approvedLeave) {
                $leaveTypeName = $approvedLeave->leaveType->name ?? 'Cuti';
                throw new \Exception('Anda sedang cuti (' . $leaveTypeName . '). Tidak perlu melakukan absensi.');
            }

            // Check if worker is on approved business trip today
            $approvedBusinessTrip = \App\Models\BusinessTrip::where('worker_id', $workerId)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();
            if ($approvedBusinessTrip) {
                throw new \Exception('Anda sedang dalam perjalanan dinas ke ' . $approvedBusinessTrip->destination . '. Tidak perlu melakukan absensi.');
            }

            // Get worker's shift for today
            $workerShift = $this->workerShiftRepository->getActiveByWorkerId($workerId);
            if (!$workerShift) {
                throw new \Exception('Tidak ada jadwal shift aktif untuk pegawai ini.');
            }

            $shift = $this->shiftRepository->findById($workerShift->shift_id);

            // Validate location
            $location = $this->locationRepository->findById($data['location_id']);

            $distance = $location->calculateDistance((float)$data['latitude'], (float)$data['longitude']);
            $isOutsideRadius = $distance > $location->radius;

            // Enforce radius only for 'present' status
            $statusForRadius = $data['status'] ?? 'present';
            if ($statusForRadius === 'present' && $isOutsideRadius) {
                throw new \Exception('Anda berada di luar radius lokasi absensi. Silakan mendekat ke lokasi yang ditentukan.');
            }

            // Calculate if late (only for present status)
            $checkInTime = now();
            $statusForLate = $data['status'] ?? 'present';
            if ($statusForLate === 'present') {
                $shiftStartTimeStr = \Carbon\Carbon::parse($shift->start_time)->format('H:i:s');
                $shiftStartDateTime = \Carbon\Carbon::parse($today . ' ' . $shiftStartTimeStr);
                $graceTime = $shiftStartDateTime->copy()->addMinutes($shift->grace_period_minutes);

                $isLate = $checkInTime->greaterThan($graceTime);
                $lateMinutes = $isLate ? $checkInTime->diffInMinutes($shiftStartDateTime) : 0;
            } else {
                $isLate = false;
                $lateMinutes = 0;
            }

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
                'check_in_by_admin' => $data['by_admin'] ?? false,
                'check_in_admin_id' => ($data['by_admin'] ?? false) ? ($data['admin_id'] ?? null) : null,
                'status' => $data['status'] ?? 'present',
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
                throw new \Exception('Data absensi tidak ditemukan.');
            }

            if ($attendance->check_out) {
                throw new \Exception('Anda sudah melakukan check-out.');
            }

            $checkOutTime = now();
            $attendanceDate = \Carbon\Carbon::parse($attendance->attendance_date);
            
            // Validasi: Tidak boleh checkout setelah hari berikutnya
            $nextDayStart = $attendanceDate->copy()->addDay()->startOfDay();
            if ($checkOutTime->greaterThanOrEqualTo($nextDayStart)) {
                throw new \Exception('Tidak dapat melakukan check-out setelah tengah malam hari berikutnya. Silakan hubungi admin untuk koreksi absensi.');
            }

            // Validasi: Harus sudah check-in terlebih dahulu
            if (!$attendance->check_in) {
                throw new \Exception('Anda belum melakukan check-in. Tidak dapat melakukan check-out.');
            }

            // Validate location
            $location = $this->locationRepository->findById($data['location_id']);
            $distance = $location->calculateDistance((float)$data['latitude'], (float)$data['longitude']);

            // Get shift information for calculating early leave
            $shift = $this->shiftRepository->findById($attendance->shift_id);
            if (!$shift) {
                throw new \Exception('Jadwal shift tidak ditemukan.');
            }

            // Calculate shift end time based on attendance date
            $shiftEndTime = \Carbon\Carbon::parse($shift->end_time)->format('H:i:s');
            $shiftEndDateTime = \Carbon\Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $shiftEndTime);

            // Jika shift melewati tengah malam (overnight), tambahkan satu hari ke tanggal akhir shift
            if ($shift->is_overnight) {
                $shiftEndDateTime->addDay();
            }

            // Hitung early leave dan overtime
            $isEarlyLeave = $checkOutTime->lessThan($shiftEndDateTime);
            $earlyLeaveMinutes = 0;
            $overtimeMinutes = 0;

            if ($isEarlyLeave) {
                $earlyLeaveMinutes = $checkOutTime->diffInMinutes($shiftEndDateTime);
                
                // Peringatan untuk pulang lebih awal
                $earlyLeaveHours = floor($earlyLeaveMinutes / 60);
                $earlyLeaveRemainingMinutes = $earlyLeaveMinutes % 60;
                $earlyLeaveText = '';
                
                if ($earlyLeaveHours > 0) {
                    $earlyLeaveText .= $earlyLeaveHours . ' jam ';
                }
                if ($earlyLeaveRemainingMinutes > 0) {
                    $earlyLeaveText .= $earlyLeaveRemainingMinutes . ' menit';
                }
                
                \Log::warning('Early check-out detected', [
                    'worker_id' => $attendance->worker_id,
                    'attendance_id' => $attendanceId,
                    'scheduled_end' => $shiftEndDateTime->format('H:i'),
                    'actual_checkout' => $checkOutTime->format('H:i'),
                    'early_minutes' => $earlyLeaveMinutes
                ]);
                
                // Optional: Bisa ditambahkan notifikasi atau approval untuk early leave
                $earlyLeaveWarning = "Perhatian: Anda pulang lebih awal {$earlyLeaveText} dari jadwal ({$shiftEndDateTime->format('H:i')}). Pastikan Anda sudah mendapat izin dari atasan.";
            } else {
                // Calculate overtime if checkout is after shift end time
                $overtimeMinutes = $checkOutTime->greaterThan($shiftEndDateTime)
                    ? $checkOutTime->diffInMinutes($shiftEndDateTime)
                    : 0;
            }

            // Update attendance
            $attendanceDTO = AttendanceDTO::fromRequest([
                'id' => $attendance->id,
                'worker_id' => $attendance->worker_id,
                'shift_id' => $attendance->shift_id,
                'location_id' => $attendance->location_id,
                'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
                'check_in' => $attendance->check_in?->format('Y-m-d H:i:s'),
                'check_out' => $checkOutTime->format('Y-m-d H:i:s'),
                'check_in_latitude' => $attendance->check_in_latitude,
                'check_in_longitude' => $attendance->check_in_longitude,
                'check_out_latitude' => $data['latitude'],
                'check_out_longitude' => $data['longitude'],
                'distance_check_in' => $attendance->distance_check_in,
                'distance_check_out' => $distance,
                'check_in_by_admin' => $attendance->check_in_by_admin,
                'check_in_admin_id' => $attendance->check_in_admin_id,
                'check_out_by_admin' => $data['by_admin'] ?? false,
                'check_out_admin_id' => ($data['by_admin'] ?? false) ? ($data['admin_id'] ?? null) : null,
                'status' => $attendance->status,
                'is_late' => $attendance->is_late,
                'late_minutes' => $attendance->late_minutes,
                'is_early_leave' => $isEarlyLeave,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'is_outside_radius' => $distance > $location->radius,
                'overtime_minutes' => $overtimeMinutes,
                'notes' => $attendance->notes . ($isEarlyLeave ? "\n[SYSTEM] Pulang lebih awal: {$earlyLeaveMinutes} menit" : ''),
            ]);

            $updated = $this->attendanceRepository->update($attendanceId, $attendanceDTO);

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
        $ext = strtolower($photo->getClientOriginalExtension() ?? 'jpg');
        $filename = sprintf('%s_%s_%s.%s', $workerId, $type, now()->format('YmdHis'), $ext);

        // Try to process with Intervention Image if available; otherwise fallback to storing original file
        try {
            if (class_exists('\\Intervention\\Image\\ImageManagerStatic')) {
                $img = Image::make($photo->getRealPath());
                $img->orientate();
                if ($img->width() > 1200) {
                    $img->resize(1200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $encoded = (string) $img->encode($ext, 75);
                $path = 'attendance-photos/' . $filename;
                Storage::disk('public')->put($path, $encoded);

                return $path;
            }
        } catch (\Throwable $e) {
            // swallow and fallback to storing original
        }

        // Fallback: store original uploaded file
        return $photo->storeAs('attendance-photos', $filename, 'public');
    }
}
