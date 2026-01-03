<?php

namespace Tests\Feature\ShiftSwap;

use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerShift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftSwapWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $requesterUser;
    protected User $targetUser;
    protected User $managerUser;
    protected Worker $requesterWorker;
    protected Worker $targetWorker;
    protected Department $department;
    protected Department $otherDepartment;
    protected Shift $shift;
    protected WorkerShift $requesterShift;
    protected WorkerShift $targetShift;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Employee']);
        Role::create(['name' => 'Manager']);

        // Create departments
        $this->department = Department::create([
            'name' => 'Department A',
            'code' => 'DEPT_A',
        ]);

        $this->otherDepartment = Department::create([
            'name' => 'Department B',
            'code' => 'DEPT_B',
        ]);

        // Create shift
        $this->shift = Shift::create([
            'name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'total_hours' => 8,
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'is_active' => true,
        ]);

        // Create requester
        $this->requesterUser = User::factory()->create();
        $this->requesterUser->assignRole('Employee');
        $this->requesterWorker = Worker::create([
            'user_id' => $this->requesterUser->id,
            'department_id' => $this->department->id,
            'name' => 'Requester Worker',
            'nip' => '111111',
            'hire_date' => Carbon::now()->subYears(2),
            'is_active' => true,
        ]);

        // Create target worker
        $this->targetUser = User::factory()->create();
        $this->targetUser->assignRole('Employee');
        $this->targetWorker = Worker::create([
            'user_id' => $this->targetUser->id,
            'department_id' => $this->otherDepartment->id, // Different department
            'name' => 'Target Worker',
            'nip' => '222222',
            'hire_date' => Carbon::now()->subYears(1),
            'is_active' => true,
        ]);

        // Create manager
        $this->managerUser = User::factory()->create();
        $this->managerUser->assignRole('Manager');
        Worker::create([
            'user_id' => $this->managerUser->id,
            'department_id' => $this->department->id,
            'name' => 'Manager',
            'nip' => '999999',
            'hire_date' => Carbon::now()->subYears(5),
            'is_active' => true,
        ]);

        // Create worker shifts (3 days from now)
        $futureDate = Carbon::now()->addDays(3);
        $this->requesterShift = WorkerShift::create([
            'worker_id' => $this->requesterWorker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => $futureDate,
            'is_active' => true,
        ]);

        $this->targetShift = WorkerShift::create([
            'worker_id' => $this->targetWorker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => $futureDate,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function employee_can_create_swap_request()
    {
        $response = $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'target_shift_id' => $this->targetShift->id,
                'reason' => 'Need to swap due to personal reasons',
            ]);

        $response->assertRedirect(route('employee.shift-swaps.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_swap_requests', [
            'requester_id' => $this->requesterWorker->id,
            'target_worker_id' => $this->targetWorker->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function target_worker_can_accept_swap_request()
    {
        // Create swap
        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'target_shift_id' => $this->targetShift->id,
                'reason' => 'Need swap',
            ]);

        $swap = \App\Models\ShiftSwapRequest::first();

        // Target accepts
        $response = $this->actingAs($this->targetUser)
            ->post(route('employee.shift-swaps.accept', $swap->id));

        $response->assertRedirect(route('employee.shift-swaps.index'));
        $response->assertSessionHas('success');

        $swap->refresh();
        // Should be awaiting_approval because cross-department
        $this->assertEquals('awaiting_approval', $swap->status);
        $this->assertTrue($swap->requires_manager_approval);
    }

    /** @test */
    public function manager_can_approve_cross_department_swap()
    {
        // Create and accept swap
        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'target_shift_id' => $this->targetShift->id,
                'reason' => 'Need swap',
            ]);

        $swap = \App\Models\ShiftSwapRequest::first();

        $this->actingAs($this->targetUser)
            ->post(route('employee.shift-swaps.accept', $swap->id));

        $swap->refresh();

        // Manager approves
        $response = $this->actingAs($this->managerUser)
            ->post(route('manager.shift-swap-approvals.approve', $swap->id), [
                'notes' => 'Approved by manager',
            ]);

        $response->assertRedirect(route('manager.shift-swap-approvals.index'));
        $response->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals('approved', $swap->status);
        $this->assertEquals($this->managerUser->id, $swap->manager_id);
        $this->assertNotNull($swap->manager_approved_at);
    }

    /** @test */
    public function manager_can_execute_approved_swap()
    {
        // Create, accept, and approve swap
        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'target_shift_id' => $this->targetShift->id,
                'reason' => 'Need swap',
            ]);

        $swap = \App\Models\ShiftSwapRequest::first();

        $this->actingAs($this->targetUser)
            ->post(route('employee.shift-swaps.accept', $swap->id));

        $swap->refresh();

        $this->actingAs($this->managerUser)
            ->post(route('manager.shift-swap-approvals.approve', $swap->id), [
                'notes' => 'Approved',
            ]);

        $swap->refresh();

        // Store original shift IDs
        $originalRequesterShiftId = $this->requesterShift->shift_id;
        $originalTargetShiftId = $this->targetShift->shift_id;

        // Execute swap
        $response = $this->actingAs($this->managerUser)
            ->post(route('manager.shift-swap-approvals.execute', $swap->id));

        $response->assertRedirect(route('manager.shift-swap-approvals.index'));
        $response->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals('executed', $swap->status);
        $this->assertNotNull($swap->executed_at);

        // Verify shifts were swapped
        $this->requesterShift->refresh();
        $this->targetShift->refresh();

        $this->assertEquals($originalTargetShiftId, $this->requesterShift->shift_id);
        $this->assertEquals($originalRequesterShiftId, $this->targetShift->shift_id);
    }

    /** @test */
    public function requester_can_cancel_pending_swap()
    {
        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'reason' => 'Need swap',
            ]);

        $swap = \App\Models\ShiftSwapRequest::first();

        $response = $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.cancel', $swap->id));

        $response->assertRedirect(route('employee.shift-swaps.index'));
        $response->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals('cancelled', $swap->status);
    }

    /** @test */
    public function target_worker_can_reject_swap()
    {
        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'reason' => 'Need swap',
            ]);

        $swap = \App\Models\ShiftSwapRequest::first();

        $response = $this->actingAs($this->targetUser)
            ->post(route('employee.shift-swaps.reject', $swap->id), [
                'reason' => 'Not available on that date',
            ]);

        $response->assertRedirect(route('employee.shift-swaps.index'));
        $response->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals('rejected', $swap->status);
    }

    /** @test */
    public function audit_logs_are_created_for_all_actions()
    {
        // Create swap
        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'target_shift_id' => $this->targetShift->id,
                'reason' => 'Need swap',
            ]);

        $swap = \App\Models\ShiftSwapRequest::first();

        // Accept
        $this->actingAs($this->targetUser)
            ->post(route('employee.shift-swaps.accept', $swap->id));

        // Approve
        $swap->refresh();
        $this->actingAs($this->managerUser)
            ->post(route('manager.shift-swap-approvals.approve', $swap->id));

        // Execute
        $swap->refresh();
        $this->actingAs($this->managerUser)
            ->post(route('manager.shift-swap-approvals.execute', $swap->id));

        // Check audit logs
        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'created',
        ]);

        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'accepted',
        ]);

        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'approved_by_manager',
        ]);

        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'executed',
        ]);

        // Should have 4 audit log entries
        $auditCount = \App\Models\ShiftSwapAuditLog::where('shift_swap_request_id', $swap->id)->count();
        $this->assertEquals(4, $auditCount);
    }
}
