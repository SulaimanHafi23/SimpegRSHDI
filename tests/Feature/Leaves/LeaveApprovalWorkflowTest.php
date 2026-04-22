<?php

namespace Tests\Feature\Leaves;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $hrUser;
    protected User $managerUserDeptA;
    protected User $managerUserDeptB;
    protected User $employeeUserDeptA;
    protected User $employeeUserDeptB;
    protected Department $departmentA;
    protected Department $departmentB;
    protected LeaveType $leaveType;
    protected LeaveRequest $pendingLeaveDeptA;
    protected LeaveRequest $pendingLeaveDeptB;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF and redirect middlewares for testing
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutMiddleware(\App\Http\Middleware\RedirectBasedOnRole::class);

        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'HR']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Employee']);

        // Create permissions (leave.manage)
        \Spatie\Permission\Models\Permission::create(['name' => 'leave.manage']);

        // Assign leave.manage permission to HR and Manager
        $hrRole = Role::where('name', 'HR')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $hrRole->givePermissionTo('leave.manage');
        $managerRole->givePermissionTo('leave.manage');

        // Create departments
        $this->departmentA = Department::create([
            'name' => 'Department A',
            'code' => 'DEPT_A',
        ]);

        $this->departmentB = Department::create([
            'name' => 'Department B',
            'code' => 'DEPT_B',
        ]);

        // Create leave type
        $this->leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'ANNUAL',
            'annual_allowance' => 12,
            'color' => '#1f2937',
        ]);

        // Create HR user
        $hrWorker = Worker::create([
            'nip' => 'HR001',
            'name' => 'HR User Test',
            'email' => 'hr@test.com',
            'phone_number' => '081234567890',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Jakarta',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'department_id' => $this->departmentA->id,
            'hire_date' => '2020-01-01',
            'employment_status' => 'permanent',
        ]);
        $this->hrUser = User::factory()->create(['worker_id' => $hrWorker->id]);
        $this->hrUser->assignRole('HR');
        $this->hrUser->givePermissionTo('leave.manage');

        // Create Manager for Department A
        $managerWorkerA = Worker::create([
            'nip' => 'MGR001A',
            'name' => 'Manager A Test',
            'email' => 'mgr-a@test.com',
            'phone_number' => '081234567891',
            'birth_date' => '1990-01-02',
            'birth_place' => 'Jakarta',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'department_id' => $this->departmentA->id,
            'hire_date' => '2020-01-01',
            'employment_status' => 'permanent',
        ]);
        $this->managerUserDeptA = User::factory()->create(['worker_id' => $managerWorkerA->id]);
        $this->managerUserDeptA->assignRole('Manager');
        $this->managerUserDeptA->givePermissionTo('leave.manage');

        // Create Manager for Department B
        $managerWorkerB = Worker::create([
            'nip' => 'MGR001B',
            'name' => 'Manager B Test',
            'email' => 'mgr-b@test.com',
            'phone_number' => '081234567892',
            'birth_date' => '1990-01-03',
            'birth_place' => 'Jakarta',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'department_id' => $this->departmentB->id,
            'hire_date' => '2020-01-01',
            'employment_status' => 'permanent',
        ]);
        $this->managerUserDeptB = User::factory()->create(['worker_id' => $managerWorkerB->id]);
        $this->managerUserDeptB->assignRole('Manager');
        $this->managerUserDeptB->givePermissionTo('leave.manage');

        // Create Employee in Department A
        $employeeWorkerA = Worker::create([
            'nip' => 'EMP001A',
            'name' => 'Employee A Test',
            'email' => 'emp-a@test.com',
            'phone_number' => '081234567893',
            'birth_date' => '1995-01-01',
            'birth_place' => 'Jakarta',
            'gender' => 'Perempuan',
            'religion' => 'Islam',
            'department_id' => $this->departmentA->id,
            'hire_date' => '2022-01-01',
            'employment_status' => 'contract',
        ]);
        $this->employeeUserDeptA = User::factory()->create(['worker_id' => $employeeWorkerA->id]);
        $this->employeeUserDeptA->assignRole('Employee');

        // Create Employee in Department B
        $employeeWorkerB = Worker::create([
            'nip' => 'EMP001B',
            'name' => 'Employee B Test',
            'email' => 'emp-b@test.com',
            'phone_number' => '081234567894',
            'birth_date' => '1995-01-02',
            'birth_place' => 'Jakarta',
            'gender' => 'Perempuan',
            'religion' => 'Islam',
            'department_id' => $this->departmentB->id,
            'hire_date' => '2022-01-01',
            'employment_status' => 'contract',
        ]);
        $this->employeeUserDeptB = User::factory()->create(['worker_id' => $employeeWorkerB->id]);
        $this->employeeUserDeptB->assignRole('Employee');

        // Create pending leave requests
        $this->pendingLeaveDeptA = LeaveRequest::create([
            'worker_id' => $employeeWorkerA->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(7),
            'total_days' => 3,
            'reason' => 'Personal leave for Department A employee',
            'status' => 'pending',
        ]);

        $this->pendingLeaveDeptB = LeaveRequest::create([
            'worker_id' => $employeeWorkerB->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(12),
            'total_days' => 3,
            'reason' => 'Personal leave for Department B employee',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function manager_can_see_approval_list_for_own_department_only()
    {
        $response = $this->actingAs($this->managerUserDeptA)
            ->get(route('approvals.leaves.index'));

        $response->assertStatus(200);
        $response->assertViewIs('approvals.leaves.index');

        // Should see own department's leave (Dept A)
        $leaves = $response->viewData('leaves');
        $departmentALeaveIds = $leaves->pluck('id')->toArray();

        $this->assertContains($this->pendingLeaveDeptA->id, $departmentALeaveIds);
        // Should NOT see Department B's leave
        $this->assertNotContains($this->pendingLeaveDeptB->id, $departmentALeaveIds);
    }

    /** @test */
    public function hr_can_see_approval_list_for_all_departments()
    {
        $response = $this->actingAs($this->hrUser)
            ->get(route('approvals.leaves.index'));

        $response->assertStatus(200);
        $response->assertViewIs('approvals.leaves.index');

        // HR should see both departments' leaves
        $leaves = $response->viewData('leaves');
        $leaveIds = $leaves->pluck('id')->toArray();

        $this->assertContains($this->pendingLeaveDeptA->id, $leaveIds);
        $this->assertContains($this->pendingLeaveDeptB->id, $leaveIds);
    }

    /** @test */
    public function manager_can_approve_leave_in_own_department()
    {
        $this->actingAs($this->managerUserDeptA)
            ->post(route('approvals.leaves.approve', $this->pendingLeaveDeptA->id), [
                'approval_notes' => 'Approved by manager A',
            ]);

        // Verify leave is approved (database change is the actual success)
        $this->pendingLeaveDeptA->refresh();
        $this->assertEquals('approved', $this->pendingLeaveDeptA->status);
        $this->assertEquals($this->managerUserDeptA->id, $this->pendingLeaveDeptA->approved_by);
        $this->assertNotNull($this->pendingLeaveDeptA->approved_at);
    }

    /** @test */
    public function manager_cannot_approve_leave_in_other_department()
    {
        $originalStatus = $this->pendingLeaveDeptB->status;
        $originalApproverId = $this->pendingLeaveDeptB->approved_by;

        try {
            $this->actingAs($this->managerUserDeptA)
                ->post(route('approvals.leaves.approve', $this->pendingLeaveDeptB->id), [
                    'approval_notes' => 'Try to approve dept B leave',
                ]);
        } catch (\Exception $e) {
            // Expected - should throw exception
        }

        // Verify leave is still pending (not approved)
        $this->pendingLeaveDeptB->refresh();
        $this->assertEquals($originalStatus, $this->pendingLeaveDeptB->status);
        $this->assertEquals($originalApproverId, $this->pendingLeaveDeptB->approved_by);
    }

    /** @test */
    public function hr_can_approve_leave_from_any_department()
    {
        $this->actingAs($this->hrUser)
            ->post(route('approvals.leaves.approve', $this->pendingLeaveDeptB->id), [
                'approval_notes' => 'Approved by HR',
            ]);

        // Verify leave is approved
        $this->pendingLeaveDeptB->refresh();
        $this->assertEquals('approved', $this->pendingLeaveDeptB->status);
        $this->assertEquals($this->hrUser->id, $this->pendingLeaveDeptB->approved_by);
    }

    /** @test */
    public function manager_can_reject_leave_in_own_department()
    {
        $this->actingAs($this->managerUserDeptA)
            ->post(route('approvals.leaves.reject', $this->pendingLeaveDeptA->id), [
                'rejection_reason' => 'Too many absences in this period',
            ]);

        // Verify leave is rejected
        $this->pendingLeaveDeptA->refresh();
        $this->assertEquals('rejected', $this->pendingLeaveDeptA->status);
        $this->assertEquals('Too many absences in this period', $this->pendingLeaveDeptA->rejection_reason);
    }

    /** @test */
    public function cannot_approve_already_approved_leave()
    {
        // First approval
        $this->actingAs($this->managerUserDeptA)
            ->post(route('approvals.leaves.approve', $this->pendingLeaveDeptA->id), [
                'approval_notes' => 'First approval',
            ]);

        // Try to approve again
        $response = $this->actingAs($this->managerUserDeptA)
            ->post(route('approvals.leaves.approve', $this->pendingLeaveDeptA->id), [
                'approval_notes' => 'Second approval',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify only one approval by first manager
        $this->pendingLeaveDeptA->refresh();
        $this->assertEquals('approved', $this->pendingLeaveDeptA->status);
        $this->assertEquals($this->managerUserDeptA->id, $this->pendingLeaveDeptA->approved_by);
    }

    /** @test */
    public function approval_index_shows_correct_statistics()
    {
        // Create another leave in Department A (approved)
        LeaveRequest::create([
            'worker_id' => $this->employeeUserDeptA->worker->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::now()->addDays(20),
            'end_date' => Carbon::now()->addDays(22),
            'total_days' => 3,
            'reason' => 'Already approved leave',
            'status' => 'approved',
            'approved_by' => $this->managerUserDeptA->id,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->managerUserDeptA)
            ->get(route('approvals.leaves.index'));

        $response->assertStatus(200);

        // For Manager A viewing own department
        $this->assertEquals(2, $response->viewData('totalLeaves')); // 1 pending + 1 approved
        $this->assertEquals(1, $response->viewData('pendingCount')); // pending count should be 1
        $this->assertEquals(1, $response->viewData('approvedCount')); // approved count should be 1
    }

    /** @test */
    public function employee_cannot_access_approval_list()
    {
        $response = $this->actingAs($this->employeeUserDeptA)
            ->get(route('approvals.leaves.index'));

        // Should be forbidden (no Manager/HR role)
        $response->assertStatus(403);
    }

    /** @test */
    public function manager_can_view_approval_detail()
    {
        $response = $this->actingAs($this->managerUserDeptA)
            ->get(route('approvals.leaves.show', $this->pendingLeaveDeptA->id));

        $response->assertStatus(200);
        $response->assertViewIs('approvals.leaves.show');

        $leave = $response->viewData('leave');
        $this->assertEquals($this->pendingLeaveDeptA->id, $leave->id);
    }

    /** @test */
    public function manager_cannot_view_approval_detail_from_other_department()
    {
        $response = $this->actingAs($this->managerUserDeptA)
            ->get(route('approvals.leaves.show', $this->pendingLeaveDeptB->id));

        // Should be forbidden (different department)
        $response->assertStatus(403);
    }
}
