<?php

namespace Tests\Feature\ShiftSwap;

use App\Models\Department;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\Shift;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerShift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    protected Worker $managerWorker;
    protected Department $department;
    protected Department $otherDepartment;
    protected Gender $gender;
    protected Religion $religion;
    protected Shift $shift;
    protected WorkerShift $requesterShift;
    protected WorkerShift $targetShift;
    protected Worker $backupWorker;
    protected WorkerShift $backupShift;
    protected int $workerSequence = 1;

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

        $this->gender = Gender::firstOrCreate(['name' => 'Laki-laki'], ['is_active' => true]);
        $this->religion = Religion::firstOrCreate(['name' => 'Islam'], ['is_active' => true]);

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
        $this->requesterWorker = $this->createWorker($this->department, 'Requester Worker');
        $this->requesterUser = User::factory()->create([
            'worker_id' => $this->requesterWorker->id,
            'name' => $this->requesterWorker->name,
        ]);
        $this->requesterUser->assignRole('Employee');

        // Create target worker
        $this->targetWorker = $this->createWorker($this->otherDepartment, 'Target Worker');
        $this->targetUser = User::factory()->create([
            'worker_id' => $this->targetWorker->id,
            'name' => $this->targetWorker->name,
        ]);
        $this->targetUser->assignRole('Employee');

        // Create manager
        $this->managerWorker = $this->createWorker($this->department, 'Manager');
        $this->managerUser = User::factory()->create([
            'worker_id' => $this->managerWorker->id,
            'name' => $this->managerWorker->name,
        ]);
        $this->managerUser->assignRole('Manager');

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

        // Keep one extra scheduled worker to satisfy minimum staffing rule.
        $this->backupWorker = $this->createWorker($this->department, 'Backup Worker');
        $this->backupShift = WorkerShift::create([
            'worker_id' => $this->backupWorker->id,
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
                'swap_type' => 'single_date',
                'swap_date' => Carbon::parse($this->requesterShift->effective_from)->format('Y-m-d'),
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
                'swap_type' => 'single_date',
                'swap_date' => Carbon::parse($this->requesterShift->effective_from)->format('Y-m-d'),
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
                'swap_type' => 'single_date',
                'swap_date' => Carbon::parse($this->requesterShift->effective_from)->format('Y-m-d'),
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
        $this->assertEquals('executed', $swap->status); // Auto-executed after approval
        $this->assertEquals($this->managerUser->id, $swap->manager_id);
        $this->assertNotNull($swap->manager_approved_at);
    }

    /** @test */
    public function manager_can_execute_approved_swap()
    {
        // Create, accept, and approve swap (which auto-executes)
        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'target_shift_id' => $this->targetShift->id,
                'swap_type' => 'single_date',
                'swap_date' => Carbon::parse($this->requesterShift->effective_from)->format('Y-m-d'),
                'reason' => 'Need swap',
            ]);

        $swap = \App\Models\ShiftSwapRequest::first();

        $this->actingAs($this->targetUser)
            ->post(route('employee.shift-swaps.accept', $swap->id));

        $swap->refresh();

        // Approve (which auto-executes the swap)
        $this->actingAs($this->managerUser)
            ->post(route('manager.shift-swap-approvals.approve', $swap->id), [
                'notes' => 'Approved',
            ]);

        $swap->refresh();

        // Verify swap was auto-executed after approval
        $this->assertEquals('executed', $swap->status);
        $this->assertNotNull($swap->executed_at);
        $this->assertNotNull($swap->manager_approved_at);

        // Verify ShiftOverride records were created
        $this->assertDatabaseHas('shift_overrides', [
            'worker_id' => $swap->requester_id,
            'shift_swap_request_id' => $swap->id,
        ]);
        $this->assertDatabaseHas('shift_overrides', [
            'worker_id' => $swap->target_worker_id,
            'shift_swap_request_id' => $swap->id,
        ]);
    }

    /** @test */
    public function requester_can_cancel_pending_swap()
    {
        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'swap_type' => 'single_date',
                'swap_date' => Carbon::parse($this->requesterShift->effective_from)->format('Y-m-d'),
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
                'swap_type' => 'single_date',
                'swap_date' => Carbon::parse($this->requesterShift->effective_from)->format('Y-m-d'),
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
                'swap_type' => 'single_date',
                'swap_date' => Carbon::parse($this->requesterShift->effective_from)->format('Y-m-d'),
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

    private function createWorker(Department $department, string $name): Worker
    {
        $seq = $this->workerSequence++;

        return Worker::create([
            'nip' => 'FT' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
            'name' => $name,
            'email' => 'feature.worker' . $seq . '@example.com',
            'phone_number' => '0813' . str_pad((string) (1000000 + $seq), 7, '0', STR_PAD_LEFT),
            'address' => 'Feature Test Address ' . $seq,
            'birth_date' => Carbon::now()->subYears(29)->format('Y-m-d'),
            'birth_place' => 'Test City',
            'gender_id' => $this->gender->id,
            'religion_id' => $this->religion->id,
            'department_id' => $department->id,
            'hire_date' => Carbon::now()->subYears(2)->format('Y-m-d'),
            'employment_status' => 'contract',
            'status' => 'active',
        ]);
    }
}
