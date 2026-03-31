<?php

namespace App\Services\Attendance;

use App\DTOs\AttendanceDTO;
use App\DTOs\AttendancePhotoDTO;
use App\Repositories\Contracts\Attendance\AttendanceRepositoryInterface;
use App\Repositories\Contracts\Attendance\AttendancePhotoRepositoryInterface;
use App\Repositories\Contracts\WorkerShift\WorkerShiftRepositoryInterface;
use App\Repositories\Contracts\Master\ShiftRepositoryInterface;
use App\Services\WorkerOffDay\WorkerOffDayService;
use Carbon\Carbon;
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
        protected ShiftRepositoryInterface $shiftRepository,
        protected WorkerOffDayService $offDayService,
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

    public function getCollectionByPeriod(string $workerId, string $dateFrom, string $dateTo, array $filters = [])
    {
        return $this->attendanceRepository->getCollectionByPeriod($workerId, $dateFrom, $dateTo, $filters);
    }

    public function checkIn(array $data)
    {
        DB::beginTransaction();
        try {
            $workerId = $data['worker_id'];
            $today = now()->format('Y-m-d');

            $worker = \App\Models\Worker::with('department')->find($workerId);
            if (!$worker) {
                throw new \Exception('Data pekerja tidak ditemukan.');
            }

            $offDayCheck = $this->offDayService->canPerformAttendance(
                $worker,
                $today,
                'check_in'
            );
            if (!$offDayCheck['can_perform']) {
                throw new \Exception($offDayCheck['message'] ?? 'Hari ini termasuk hari libur Anda.');
            }

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
                // Cek apakah departemen pegawai ini tetap wajib hadir saat libur nasional
                $deptRequiresAttendance = $worker->department && $worker->department->requires_holiday_attendance;

                if (!$deptRequiresAttendance) {
                    throw new \Exception('Hari ini adalah libur nasional (' . $holiday->name . '). Anda tidak perlu melakukan absensi.');
                }
                // Jika departemen standby (requires_holiday_attendance = true), lanjutkan proses check-in
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

            $status = $data['status'] ?? 'present';

            // Get worker's shift for today (check ShiftOverride first, then active shift)
            $shiftOverride = $worker ? $worker->shiftOverrides()
                ->where('override_date', $today)
                ->with('shift')
                ->first() : null;

            if ($shiftOverride && $shiftOverride->shift) {
                $shift = $shiftOverride->shift;
            } else {
                $workerShift = $this->workerShiftRepository->getActiveByWorkerId($workerId);
                if (!$workerShift) {
                    throw new \Exception('Tidak ada jadwal shift aktif untuk pegawai ini.');
                }

                $shift = $this->shiftRepository->findById($workerShift->shift_id);
                if (!$shift) {
                    throw new \Exception('Jadwal shift tidak ditemukan.');
                }
            }

            $schedule = $shift->getScheduleForDate($today);

            // ========== VALIDATE CHECK-IN TIME WINDOW ==========
            $checkInTime = now();
            $shiftStartTimeStr = $schedule['start_time'];
            $shiftStartDateTime = \Carbon\Carbon::parse($today . ' ' . $shiftStartTimeStr);

            // Special handling for overnight shifts
            // If shift is overnight (e.g., 22:00-06:00) and current time is in the morning,
            // the shift actually starts tonight, not this morning
            if ($schedule['is_overnight']) {
                $shiftStartHour = (int) \Carbon\Carbon::parse($shiftStartTimeStr)->format('H');
                $currentHour = $checkInTime->hour;

                // If current time is before noon and shift starts after noon (evening shift),
                // the shift hasn't started yet (it will start tonight)
                if ($currentHour < 12 && $shiftStartHour >= 12) {
                    // Shift starts tonight, so add 0 days (today evening)
                    // But we're checking in the morning, which is ~12+ hours too early
                    $shiftStartDateTime = \Carbon\Carbon::parse($today . ' ' . $shiftStartTimeStr);
                } elseif ($currentHour >= 12 && $shiftStartHour >= 12) {
                    // Both in evening - shift starts today
                    $shiftStartDateTime = \Carbon\Carbon::parse($today . ' ' . $shiftStartTimeStr);
                } else {
                    // Current time is afternoon/evening, shift started last night
                    // This is valid - shift started yesterday evening
                    $shiftStartDateTime = \Carbon\Carbon::parse($today . ' ' . $shiftStartTimeStr)->subDay();
                }
            }

            // Skip time window restrictions for non-present statuses
            if ($status === 'present') {
                // Get time window configuration
                $checkInWindowBeforeHours = config('attendance.check_in_window_before_hours', 2);
                $earlyCheckInGraceMinutes = config('attendance.early_checkin_grace_minutes', 30);
                $strictTimeWindow = config('attendance.strict_time_window', false);

                // Calculate earliest allowed check-in time
                $earliestCheckInTime = $shiftStartDateTime->copy()->subHours($checkInWindowBeforeHours);
                $veryEarlyCheckInTime = $earliestCheckInTime->copy()->subMinutes($earlyCheckInGraceMinutes);

                // Validation: Too early check-in
                if ($checkInTime->lessThan($veryEarlyCheckInTime)) {
                    $hoursDiff = $checkInTime->diffInHours($shiftStartDateTime);
                    $minutesDiff = $checkInTime->diffInMinutes($shiftStartDateTime) % 60;

                    $message = sprintf(
                        'Check-in terlalu dini! Anda mencoba check-in %d jam %d menit sebelum shift dimulai (pukul %s). ' .
                        'Batas check-in paling awal adalah %d jam sebelum shift (pukul %s).',
                        $hoursDiff,
                        $minutesDiff,
                        $shiftStartDateTime->format('H:i'),
                        $checkInWindowBeforeHours,
                        $earliestCheckInTime->format('H:i')
                    );

                    if ($strictTimeWindow) {
                        throw new \Exception($message);
                    } else {
                        // Log warning but allow (non-strict mode)
                        \Log::warning('Very early check-in attempt', [
                            'worker_id' => $workerId,
                            'check_in_time' => $checkInTime->format('Y-m-d H:i:s'),
                            'shift_start' => $shiftStartDateTime->format('Y-m-d H:i:s'),
                            'earliest_allowed' => $veryEarlyCheckInTime->format('Y-m-d H:i:s'),
                            'hours_too_early' => $hoursDiff,
                            'is_overnight_shift' => $schedule['is_overnight'],
                        ]);
                    }
                } elseif ($checkInTime->lessThan($earliestCheckInTime)) {
                    // Warning for slightly early check-in (within grace period)
                    \Log::info('Early check-in (within grace period)', [
                        'worker_id' => $workerId,
                        'check_in_time' => $checkInTime->format('Y-m-d H:i:s'),
                        'shift_start' => $shiftStartDateTime->format('Y-m-d H:i:s'),
                        'earliest_allowed' => $earliestCheckInTime->format('Y-m-d H:i:s'),
                        'is_overnight_shift' => $schedule['is_overnight'],
                    ]);
                }
            }

            // Validate against single configured attendance location from ENV.
            $configuredLocation = $this->getConfiguredLocation();
            $distance = $this->calculateDistance(
                $configuredLocation['latitude'],
                $configuredLocation['longitude'],
                (float) $data['latitude'],
                (float) $data['longitude']
            );
            $isOutsideRadius = $distance > $configuredLocation['radius'];

            // Enforce radius only for 'present' status
            $statusForRadius = $status;
            if ($statusForRadius === 'present' && $isOutsideRadius) {
                throw new \Exception('Anda berada di luar radius lokasi absensi. Silakan mendekat ke lokasi yang ditentukan.');
            }

            // Calculate if late (only for present status)
            $statusForLate = $status;
            if ($statusForLate === 'present') {
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
                'attendance_date' => $today,
                'check_in' => $checkInTime,
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

            $worker = \App\Models\Worker::find($attendance->worker_id);
            if (!$worker) {
                throw new \Exception('Data pekerja tidak ditemukan.');
            }

            $offDayCheck = $this->offDayService->canPerformAttendance(
                $worker,
                now()->format('Y-m-d'),
                'check_out',
                $attendance->attendance_date?->format('Y-m-d')
            );
            if (!$offDayCheck['can_perform']) {
                throw new \Exception($offDayCheck['message'] ?? 'Tidak dapat check-out di hari libur.');
            }

            if ($attendance->check_out) {
                throw new \Exception('Anda sudah melakukan check-out.');
            }

            $checkOutTime = now();
            $attendanceDate = \Carbon\Carbon::parse($attendance->attendance_date);

            // Validasi: Harus sudah check-in terlebih dahulu
            if (!$attendance->check_in) {
                throw new \Exception('Anda belum melakukan check-in. Tidak dapat melakukan check-out.');
            }

            // Get shift info early for date validation
            $shift = $this->shiftRepository->findById($attendance->shift_id);
            if (!$shift) {
                throw new \Exception('Jadwal shift tidak ditemukan.');
            }

            $schedule = $shift->getScheduleForDate($attendance->attendance_date);
            $shiftEndTime = $schedule['end_time'];
            $shiftEndDateTime = \Carbon\Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $shiftEndTime);

            // Jika shift melewati tengah malam (overnight), tambahkan satu hari ke tanggal akhir shift
            if ($schedule['is_overnight']) {
                $shiftEndDateTime->addDay();
            }

            // Validasi: Tidak boleh checkout terlalu lama setelah shift berakhir
            // Max window = shift end + checkout window + overtime buffer
            $checkOutWindowAfterHours = (int) config('attendance.check_out_window_after_hours', 4);

            // Check for approved overtime request
            $hasApprovedOvertime = \App\Models\OvertimeRequest::where('worker_id', $attendance->worker_id)
                ->where('status', 'approved')
                ->whereDate('overtime_date', $attendance->attendance_date)
                ->exists();

            $maxCheckoutTime = $shiftEndDateTime->copy()->addHours($checkOutWindowAfterHours + ($hasApprovedOvertime ? 2 : 0));
            $isAdminCheckout = (bool) ($data['by_admin'] ?? false);

            if (!$isAdminCheckout && $checkOutTime->greaterThan($maxCheckoutTime)) {
                $hoursDiff = $shiftEndDateTime->diffInHours($checkOutTime);
                throw new \Exception(
                    "Check-out terlalu terlambat ({$hoursDiff} jam setelah shift berakhir pukul {$shiftEndDateTime->format('H:i')}). " .
                    "Batas checkout adalah {$maxCheckoutTime->format('d M Y H:i')}. " .
                    "Silakan hubungi admin untuk koreksi absensi."
                );
            }

            if ($isAdminCheckout && $checkOutTime->greaterThan($maxCheckoutTime)) {
                \Log::info('Admin bypassed checkout window', [
                    'attendance_id' => $attendanceId,
                    'worker_id' => $attendance->worker_id,
                    'admin_id' => $data['admin_id'] ?? null,
                    'max_checkout_time' => $maxCheckoutTime->format('Y-m-d H:i:s'),
                    'actual_checkout_time' => $checkOutTime->format('Y-m-d H:i:s'),
                ]);
            }

            // Validate against single configured attendance location from ENV.
            $configuredLocation = $this->getConfiguredLocation();
            $distance = $this->calculateDistance(
                $configuredLocation['latitude'],
                $configuredLocation['longitude'],
                (float) $data['latitude'],
                (float) $data['longitude']
            );

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

            $existingNotes = trim((string) $attendance->notes);
            $noteLines = [];

            if ($isEarlyLeave) {
                $noteLines[] = "[SYSTEM] Pulang lebih awal: {$earlyLeaveMinutes} menit";
            }

            if ($isAdminCheckout) {
                $adminName = \App\Models\User::find($data['admin_id'] ?? null)?->name ?? 'Admin';
                $adminNote = trim((string) ($data['admin_checkout_note'] ?? ''));
                $adminAudit = "[ADMIN] Check-out dicatat oleh {$adminName}";
                if ($adminNote !== '') {
                    $adminAudit .= ". Keterangan: {$adminNote}";
                }
                $noteLines[] = $adminAudit;
            }

            $combinedNotes = trim(implode("\n", array_filter(array_merge([$existingNotes], $noteLines))));

            // Update attendance
            $attendanceDTO = AttendanceDTO::fromRequest([
                'id' => $attendance->id,
                'worker_id' => $attendance->worker_id,
                'shift_id' => $attendance->shift_id,
                'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
                'check_in' => $attendance->check_in?->format('Y-m-d H:i:s'),
                'check_out' => $checkOutTime->format('Y-m-d H:i:s'),
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
                'is_outside_radius' => $distance > $configuredLocation['radius'],
                'overtime_minutes' => $overtimeMinutes,
                'notes' => $combinedNotes,
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
        $existing = $this->attendanceRepository->getById($id);

        if (!$existing) {
            throw new \Exception('Data absensi tidak ditemukan.');
        }

        $normalized = [
            'id' => $existing->id,
            'worker_id' => $data['worker_id'] ?? $existing->worker_id,
            'shift_id' => $data['shift_id'] ?? $existing->shift_id,
            'attendance_date' => $data['attendance_date'] ?? $data['date'] ?? $existing->attendance_date?->format('Y-m-d'),
            'check_in' => $this->normalizeDateTime($data['check_in'] ?? $existing->check_in),
            'check_out' => array_key_exists('check_out', $data)
                ? $this->normalizeDateTime($data['check_out'])
                : $this->normalizeDateTime($existing->check_out),
            'distance_check_in' => $data['distance_check_in'] ?? $existing->distance_check_in,
            'distance_check_out' => $data['distance_check_out'] ?? $existing->distance_check_out,
            'check_in_by_admin' => $data['check_in_by_admin'] ?? $existing->check_in_by_admin,
            'check_in_admin_id' => $data['check_in_admin_id'] ?? $existing->check_in_admin_id,
            'check_out_by_admin' => $data['check_out_by_admin'] ?? $existing->check_out_by_admin,
            'check_out_admin_id' => $data['check_out_admin_id'] ?? $existing->check_out_admin_id,
            'status' => $data['status'] ?? $existing->status,
            'is_late' => $data['is_late'] ?? $existing->is_late,
            'late_minutes' => $data['late_minutes'] ?? $existing->late_minutes,
            'is_early_leave' => $data['is_early_leave'] ?? $existing->is_early_leave,
            'early_leave_minutes' => $data['early_leave_minutes'] ?? $existing->early_leave_minutes,
            'is_outside_radius' => $data['is_outside_radius'] ?? $existing->is_outside_radius,
            'overtime_minutes' => $data['overtime_minutes'] ?? $existing->overtime_minutes,
            'notes' => $data['notes'] ?? $existing->notes,
        ];

        $dto = AttendanceDTO::fromRequest($normalized);
        return $this->attendanceRepository->update($id, $dto);
    }

    private function normalizeDateTime($value): ?string
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        return Carbon::parse((string) $value)->format('Y-m-d H:i:s');
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
                if ($img->width() > 800) {
                    $img->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $encoded = (string) $img->encode($ext, 70);
                $path = 'attendance-photos/' . $filename;
                Storage::disk('public')->put($path, $encoded);

                return $path;
            }
        } catch (\Throwable $e) {
            \Log::warning('Image processing failed, storing original: ' . $e->getMessage());
        }

        // Fallback: store original uploaded file
        return $photo->storeAs('attendance-photos', $filename, 'public');
    }

    /**
     * Get pending checkouts (workers who have checked in but not checked out yet)
     * Filtered to show only those whose shift has ended
     *
     * @param string|null $workerId If provided, only check this specific worker
     * @param int $hoursThreshold How many hours after shift end to consider as "pending" (default: 0)
     * @return \Illuminate\Support\Collection
     */
    public function getPendingCheckouts(?string $workerId = null, int $hoursThreshold = 0, bool $onlyActionable = false)
    {
        $now = now();

        // Get all attendances with check_in but no check_out, status = 'present'
        $query = \App\Models\Attendance::with([
            'worker.workerShifts.shift',
            'worker.shiftOverrides.shift'
        ])
        ->whereNotNull('check_in')
        ->whereNull('check_out')
        ->where('status', 'present');

        if ($workerId) {
            $query->where('worker_id', $workerId);
        }

        $pendingAttendances = $query->get();

        $pendingCheckouts = collect();

        foreach ($pendingAttendances as $attendance) {
            $worker = $attendance->worker;
            if (!$worker) continue;

            $attendanceDate = \Carbon\Carbon::parse($attendance->attendance_date);

            // Find the shift for this attendance date (same logic as checkout)
            // Check ShiftOverride first — filter from already-loaded relation
            $shiftOverride = $worker->shiftOverrides->first(function ($o) use ($attendanceDate) {
                $overrideDate = $o->override_date instanceof \Carbon\Carbon
                    ? $o->override_date->format('Y-m-d')
                    : $o->override_date;
                return $overrideDate === $attendanceDate->format('Y-m-d');
            });

            $shift = null;
            if ($shiftOverride && $shiftOverride->shift) {
                $shift = $shiftOverride->shift;
            } else {
                // Use regular shift schedule
                $activeShift = $worker->workerShifts->first(function($ws) use ($attendanceDate) {
                    return $ws->isActiveOnDate($attendanceDate);
                });

                if ($activeShift) {
                    $shift = $activeShift->shift;
                }
            }

            if (!$shift) continue;

            // Get properly formatted schedule for the date
            $schedule = $shift->getScheduleForDate($attendanceDate);

            // Calculate shift end time
            $shiftEndTime = \Carbon\Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $schedule['end_time']);

            // Handle overnight shifts
            if ($schedule['is_overnight']) {
                $shiftEndTime->addDay();
            }

            // Add threshold hours
            $thresholdTime = $shiftEndTime->copy()->addHours($hoursThreshold);

            // Only include if shift has ended (past threshold)
            if ($now->greaterThan($thresholdTime)) {
                $hoursLate = $now->diffInHours($shiftEndTime);

                $checkOutWindowAfterHours = (int) config('attendance.check_out_window_after_hours', 4);
                $hasApprovedOvertime = \App\Models\OvertimeRequest::where('worker_id', $worker->id)
                    ->where('status', 'approved')
                    ->whereDate('overtime_date', $attendanceDate->format('Y-m-d'))
                    ->exists();

                $maxCheckoutTime = $shiftEndTime->copy()->addHours($checkOutWindowAfterHours + ($hasApprovedOvertime ? 2 : 0));
                $isWindowExpired = $now->greaterThan($maxCheckoutTime);

                if ($onlyActionable && $isWindowExpired) {
                    continue;
                }

                $pendingCheckouts->push([
                    'attendance_id' => $attendance->id,
                    'worker_id' => $worker->id,
                    'worker_name' => $worker->name,
                    'position' => $worker->department->name ?? '-',
                    'attendance_date' => $attendanceDate->format('Y-m-d'),
                    'check_in_time' => \Carbon\Carbon::parse($attendance->check_in)->format('H:i'),
                    'shift_name' => $shift->name,
                    'shift_end_time' => $shiftEndTime->format('Y-m-d H:i'),
                    'hours_late' => $hoursLate,
                    'formatted_late' => $this->formatHoursLate($hoursLate),
                    'max_checkout_time' => $maxCheckoutTime->format('Y-m-d H:i'),
                    'is_window_expired' => $isWindowExpired,
                    'can_checkout' => !$isWindowExpired,
                ]);
            }
        }

        return $pendingCheckouts->sortByDesc('hours_late');
    }

    /**
     * Format hours late into human-readable string
     */
    private function formatHoursLate(int $hours): string
    {
        if ($hours < 1) {
            return 'Baru berakhir';
        } elseif ($hours < 24) {
            return $hours . ' jam yang lalu';
        } else {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            if ($remainingHours > 0) {
                return $days . ' hari ' . $remainingHours . ' jam yang lalu';
            }
            return $days . ' hari yang lalu';
        }
    }

    /**
     * Read single attendance location from config/env.
     *
     * @return array{name:string,latitude:float,longitude:float,radius:int,enforce_geofence:bool}
     */
    private function getConfiguredLocation(): array
    {
        return [
            'name' => (string) config('attendance.location.name', 'Lokasi Utama'),
            'latitude' => (float) config('attendance.location.latitude', 0),
            'longitude' => (float) config('attendance.location.longitude', 0),
            'radius' => (int) config('attendance.location.radius', 100),
            'enforce_geofence' => (bool) config('attendance.location.enforce_geofence', true),
        ];
    }

    /**
     * Calculate distance between two coordinates in meters (Haversine).
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2)
            + cos($latFrom) * cos($latTo)
            * sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
