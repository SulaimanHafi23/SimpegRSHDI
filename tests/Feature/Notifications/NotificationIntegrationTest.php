<?php

namespace Tests\Feature\Notifications;

use App\Models\Department;
use App\Models\Gender;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use App\Models\Religion;
use App\Models\Shift;
use App\Models\ShiftOverride;
use App\Models\User;
use App\Models\Worker;
use App\Notifications\AttendanceReminderNotification;
use App\Notifications\HolidayNotification;
use App\Notifications\LeaveRequestNotification;
use App\Notifications\OvertimeRequestNotification;
use App\Notifications\ShiftChangeNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function createWorkerAndUser(string $suffix): array
    {
        $gender = Gender::firstOrCreate(['name' => 'Laki-laki']);
        $religion = Religion::firstOrCreate(['name' => 'Islam']);
        $department = Department::firstOrCreate(
            ['code' => 'DPT-' . $suffix],
            ['name' => 'Dept Test ' . $suffix, 'is_active' => true]
        );

        $worker = Worker::create([
            'nip' => 'NIP-' . $suffix,
            'name' => 'Pegawai ' . $suffix,
            'email' => 'worker' . strtolower($suffix) . '@example.com',
            'phone_number' => '08123' . str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
            'address' => 'Alamat Test',
            'birth_date' => now()->subYears(25)->toDateString(),
            'birth_place' => 'Ternate',
            'gender_id' => $gender->id,
            'religion_id' => $religion->id,
            'department_id' => $department->id,
            'hire_date' => now()->subYear()->toDateString(),
            'employment_status' => 'contract',
            'status' => 'active',
        ]);

        $user = User::create([
            'worker_id' => $worker->id,
            'email' => 'user' . strtolower($suffix) . '@example.com',
            'username' => 'user_' . strtolower($suffix),
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        return [$worker, $user];
    }

    public function test_overtime_notification_can_be_sent_for_approved()
    {
        Notification::fake();
        [$worker, $user] = $this->createWorkerAndUser('A01');

        $overtime = OvertimeRequest::create([
            'worker_id' => $worker->id,
            'overtime_date' => today(),
            'start_time' => '17:00:00',
            'end_time' => '20:00:00',
            'total_hours' => 3,
            'reason' => 'Lembur test',
            'status' => 'approved',
        ]);

        Notification::send($user, new OvertimeRequestNotification($overtime, 'approved'));
        Notification::assertSentTo($user, OvertimeRequestNotification::class);
        $this->assertTrue(true);
    }

    public function test_leave_notification_can_be_sent_for_approved()
    {
        Notification::fake();
        [$worker, $user] = $this->createWorkerAndUser('A02');

        $leaveType = LeaveType::firstOrCreate(
            ['code' => 'CTH-A02'],
            [
                'name' => 'Cuti Tahunan',
                'max_days_per_year' => 12,
                'requires_approval' => true,
                'requires_attachment' => false,
                'days_notice' => 0,
                'is_active' => true,
            ]
        );

        $leave = LeaveRequest::create([
            'worker_id' => $worker->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => today()->addDay(),
            'end_date' => today()->addDays(2),
            'total_days' => 2,
            'reason' => 'Cuti test',
            'status' => 'approved',
        ]);

        Notification::send($user, new LeaveRequestNotification($leave, 'approved'));
        Notification::assertSentTo($user, LeaveRequestNotification::class);
        $this->assertTrue(true);
    }

    public function test_attendance_reminder_notification_can_be_sent()
    {
        Notification::fake();
        [$worker, $user] = $this->createWorkerAndUser('A03');

        Notification::send($user, new AttendanceReminderNotification(
            'check_in',
            'Shift Pagi',
            Carbon::parse('07:00:00'),
            $worker->name
        ));

        Notification::assertSentTo($user, AttendanceReminderNotification::class);
        $this->assertTrue(true);
    }

    public function test_holiday_notification_can_be_sent()
    {
        Notification::fake();
        [, $user] = $this->createWorkerAndUser('A04');

        $holiday = Holiday::create([
            'name' => 'Hari Libur Test',
            'date' => today()->addDays(5),
            'is_national' => true,
            'notify_users' => true,
        ]);

        Notification::send($user, new HolidayNotification($holiday, 'upcoming'));
        Notification::assertSentTo($user, HolidayNotification::class);
        $this->assertTrue(true);
    }

    public function test_shift_change_notification_can_be_sent()
    {
        Notification::fake();
        [$worker, $user] = $this->createWorkerAndUser('A05');

        $shift = Shift::create([
            'name' => 'Shift Pagi',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'total_hours' => 8,
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        $shiftOverride = ShiftOverride::create([
            'worker_id' => $worker->id,
            'shift_id' => $shift->id,
            'override_date' => today()->addDay(),
            'reason' => 'Penyesuaian test',
            'created_by' => $user->id,
        ]);

        Notification::send($user, new ShiftChangeNotification($shiftOverride, 'created'));
        Notification::assertSentTo($user, ShiftChangeNotification::class);
        $this->assertTrue(true);
    }
}
