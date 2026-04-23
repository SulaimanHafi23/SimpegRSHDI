<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendancePhoto;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnhancedAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds dengan berbagai skenario attendance
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive attendance data...');

        $workers = Worker::with('workerShifts')->get();

        if ($workers->isEmpty()) {
            $this->command->warn('No workers found! Please run WorkerSeeder first.');
            return;
        }

        // Seed untuk 3 bulan terakhir
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now();

        foreach ($workers as $worker) {
            $this->command->info("Creating attendance for: {$worker->name}");

            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // Skip Sundays (hari libur)
                if ($currentDate->isSunday()) {
                    $currentDate->addDay();
                    continue;
                }

                // Get shift for this date
                $workerShift = $worker->workerShifts()
                    ->where('effective_from', '<=', $currentDate)
                    ->orderBy('effective_from', 'desc')
                    ->first();

                if (!$workerShift || !$workerShift->shift) {
                    $currentDate->addDay();
                    continue;
                }

                $shift = $workerShift->shift;

                // Tentukan skenario untuk variasi data
                $scenario = $this->determineScenario($currentDate);

                if ($scenario === 'absent') {
                    // Tidak ada attendance record (absent)
                    $currentDate->addDay();
                    continue;
                }

                // Create attendance record
                $attendance = $this->createAttendance($worker, $shift, $currentDate, $scenario);

                // Create photos (75% kemungkinan)
                if (rand(1, 100) <= 75 && $attendance) {
                    $this->createAttendancePhotos($attendance, $scenario);
                }

                $currentDate->addDay();
            }
        }

        $this->command->info('✅ Enhanced attendance data created successfully!');
    }

    /**
     * Determine scenario untuk variasi data
     */
    private function determineScenario(Carbon $date): string
    {
        $rand = rand(1, 100);

        // Lebih banyak absent di hari Senin/Jumat
        if (($date->isMonday() || $date->isFriday()) && $rand <= 5) {
            return 'absent';
        }

        // 3% kemungkinan absent
        if ($rand <= 3) {
            return 'absent';
        }

        // 15% kemungkinan terlambat
        if ($rand <= 18) {
            return 'late';
        }

        // 10% kemungkinan pulang cepat
        if ($rand <= 28) {
            return 'early_out';
        }

        // 5% kemungkinan terlambat dan pulang cepat
        if ($rand <= 33) {
            return 'late_and_early';
        }

        // 2% kemungkinan lupa check in/out
        if ($rand <= 35) {
            return 'missing_checkout';
        }

        if ($rand <= 37) {
            return 'missing_checkin';
        }

        // Sisanya normal
        return 'normal';
    }

    /**
     * Create attendance record berdasarkan scenario
     */
    private function createAttendance(Worker $worker, $shift, Carbon $date, string $scenario)
    {
        $checkInTime = Carbon::parse($shift->start_time);
        $checkOutTime = Carbon::parse($shift->end_time);

        // Adjust untuk overnight shift
        if ($checkOutTime->lt($checkInTime)) {
            $checkOutTime->addDay();
        }

        $attendanceDate = $date->copy();
        $checkIn = $attendanceDate->copy()
            ->setHour($checkInTime->hour)
            ->setMinute($checkInTime->minute)
            ->setSecond(0);

        $checkOut = $attendanceDate->copy()
            ->setHour($checkOutTime->hour)
            ->setMinute($checkOutTime->minute)
            ->setSecond(0);

        if ($checkOut->lt($checkIn)) {
            $checkOut->addDay();
        }

        $lateMinutes = 0;
        $earlyMinutes = 0;
        $isLate = false;
        $isEarlyLeave = false;
        $status = 'present';
        $notes = null;

        switch ($scenario) {
            case 'late':
                // Terlambat 5-120 menit
                $lateMinutes = rand(5, 120);
                $checkIn->addMinutes($lateMinutes);
                $isLate = true;
                $notes = "Terlambat {$lateMinutes} menit";
                break;

            case 'early_out':
                // Pulang cepat 10-90 menit
                $earlyMinutes = rand(10, 90);
                $checkOut->subMinutes($earlyMinutes);
                $isEarlyLeave = true;
                $notes = "Pulang {$earlyMinutes} menit lebih awal";
                break;

            case 'late_and_early':
                $lateMinutes = rand(5, 60);
                $earlyMinutes = rand(10, 60);
                $checkIn->addMinutes($lateMinutes);
                $checkOut->subMinutes($earlyMinutes);
                $isLate = true;
                $isEarlyLeave = true;
                $notes = "Terlambat {$lateMinutes} menit, pulang {$earlyMinutes} menit lebih awal";
                break;

            case 'missing_checkout':
                $checkOut = null;
                $status = 'present';
                $notes = 'Lupa check out';
                break;

            case 'missing_checkin':
                // Kolom check_in wajib terisi pada skema terbaru.
                $notes = 'Anomali: check-in tercatat otomatis';
                break;

            case 'normal':
                // Normal bisa sedikit lebih awal/lambat (toleransi)
                $variance = rand(-5, 5);
                $checkIn->addMinutes($variance);
                $checkOut->addMinutes(rand(-5, 10));
                break;
        }

        return Attendance::create([
            'id' => Str::uuid(),
            'worker_id' => $worker->id,
            'attendance_date' => $date->format('Y-m-d'),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'distance_check_in' => rand(10, 150),
            'distance_check_out' => $checkOut ? rand(10, 150) : null,
            'status' => $status,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'is_early_leave' => $isEarlyLeave,
            'early_leave_minutes' => $earlyMinutes,
            'notes' => $notes,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    /**
     * Create attendance photos
     */
    private function createAttendancePhotos($attendance, string $scenario)
    {
        // Check in photo
        if ($attendance->check_in && $scenario !== 'missing_checkin') {
            AttendancePhoto::create([
                'id' => Str::uuid(),
                'attendance_id' => $attendance->id,
                'photo_path' => 'attendance/sample_checkin_' . rand(1, 5) . '.jpg',
                'photo_type' => 'check_in',
                'taken_at' => $attendance->check_in,
                'created_at' => $attendance->check_in,
                'updated_at' => $attendance->check_in,
            ]);
        }

        // Check out photo
        if ($attendance->check_out && $scenario !== 'missing_checkout') {
            AttendancePhoto::create([
                'id' => Str::uuid(),
                'attendance_id' => $attendance->id,
                'photo_path' => 'attendance/sample_checkout_' . rand(1, 5) . '.jpg',
                'photo_type' => 'check_out',
                'taken_at' => $attendance->check_out,
                'created_at' => $attendance->check_out,
                'updated_at' => $attendance->check_out,
            ]);
        }
    }
}
