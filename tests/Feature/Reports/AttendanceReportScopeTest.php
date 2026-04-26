<?php

namespace Tests\Feature\Reports;

use App\Exports\ReportAttendanceExport;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceReportScopeTest extends TestCase
{
    use RefreshDatabase;

    protected User $managerUser;
    protected User $hrUser;
    protected Worker $workerDeptA;
    protected Worker $workerDeptB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\RedirectBasedOnRole::class);

        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'HR']);

        Permission::create(['name' => 'report.view']);
        Permission::create(['name' => 'attendance.manage']);
        Permission::create(['name' => 'dashboard.manager']);
        Permission::create(['name' => 'dashboard.hr']);

        $managerRole = Role::where('name', 'Manager')->first();
        $managerRole->givePermissionTo('report.view');
        $managerRole->givePermissionTo('attendance.manage');
        $managerRole->givePermissionTo('dashboard.manager');

        $hrRole = Role::where('name', 'HR')->first();
        $hrRole->givePermissionTo('report.view');
        $hrRole->givePermissionTo('dashboard.hr');

        $deptA = Department::create([
            'name' => 'Dept A',
            'code' => 'DPT-A',
        ]);

        $deptB = Department::create([
            'name' => 'Dept B',
            'code' => 'DPT-B',
        ]);

        $managerWorker = $this->createWorker('MGR001', 'Manager A', 'manager.a@test.local', '081111111111', $deptA->id);
        $hrWorker = $this->createWorker('HR001', 'HR User', 'hr.user@test.local', '082222222222', $deptA->id);

        $this->workerDeptA = $this->createWorker('EMP001A', 'Employee A', 'employee.a@test.local', '083333333333', $deptA->id);
        $this->workerDeptB = $this->createWorker('EMP001B', 'Employee B', 'employee.b@test.local', '084444444444', $deptB->id);

        $this->managerUser = User::factory()->create(['worker_id' => $managerWorker->id]);
        $this->managerUser->assignRole('Manager');
        $this->managerUser->givePermissionTo('attendance.manage');
        $this->managerUser->givePermissionTo('report.view');
        $this->managerUser->givePermissionTo('dashboard.manager');

        $this->hrUser = User::factory()->create(['worker_id' => $hrWorker->id]);
        $this->hrUser->assignRole('HR');
        $this->hrUser->givePermissionTo('report.view');
        $this->hrUser->givePermissionTo('dashboard.hr');

        $this->createAttendance($this->workerDeptA->id, Carbon::today());
        $this->createAttendance($this->workerDeptB->id, Carbon::today());
    }

    #[Test]
    public function manager_can_only_see_attendance_from_own_department(): void
    {
        $response = $this->actingAs($this->managerUser)
            ->get(route('reports.attendance', [
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->endOfMonth()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.attendance');

        $attendances = collect($response->viewData('attendances')->items());
        $workerIds = $attendances->pluck('worker_id')->all();

        $this->assertContains($this->workerDeptA->id, $workerIds);
        $this->assertNotContains($this->workerDeptB->id, $workerIds);
    }

    #[Test]
    public function hr_can_see_attendance_from_all_departments(): void
    {
        $response = $this->actingAs($this->hrUser)
            ->get(route('reports.attendance', [
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->endOfMonth()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.attendance');

        $attendances = collect($response->viewData('attendances')->items());
        $workerIds = $attendances->pluck('worker_id')->all();

        $this->assertContains($this->workerDeptA->id, $workerIds);
        $this->assertContains($this->workerDeptB->id, $workerIds);
    }

    #[Test]
    public function manager_cannot_override_department_filter_when_exporting_attendance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-15 08:00:00'));
        Excel::fake();

        $response = $this->actingAs($this->managerUser)
            ->get(route('reports.attendance.export', [
                'format' => 'csv',
                'department_id' => $this->workerDeptB->department_id,
                'date_from' => now()->startOfMonth()->format('Y-m-d'),
                'date_to' => now()->endOfMonth()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);

        Excel::assertDownloaded('laporan-presensi-2026-01-15-080000.csv', function (ReportAttendanceExport $export) {
            $reflection = new \ReflectionClass($export);
            $filtersProperty = $reflection->getProperty('filters');
            $filtersProperty->setAccessible(true);
            $filters = $filtersProperty->getValue($export);

            return ($filters['department_id'] ?? null) === $this->managerUser->worker->department_id;
        });

        Carbon::setTestNow();
    }

    private function createWorker(string $nip, string $name, string $email, string $phone, string $departmentId): Worker
    {
        return Worker::create([
            'nip' => $nip,
            'name' => $name,
            'email' => $email,
            'phone_number' => $phone,
            'birth_date' => '1990-01-01',
            'birth_place' => 'Jakarta',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'department_id' => $departmentId,
            'hire_date' => '2020-01-01',
            'employment_status' => 'permanent',
            'status' => 'active',
        ]);
    }

    private function createAttendance(string $workerId, Carbon $date): Attendance
    {
        return Attendance::create([
            'worker_id' => $workerId,
            'attendance_date' => $date->toDateString(),
            'check_in' => $date->copy()->setTime(8, 0, 0),
            'distance_check_in' => 10,
            'status' => 'present',
            'is_late' => false,
            'late_minutes' => 0,
            'check_in_by_admin' => false,
            'check_out_by_admin' => false,
        ]);
    }
}
