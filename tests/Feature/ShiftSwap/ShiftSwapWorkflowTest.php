<?php

namespace Tests\Feature\ShiftSwap;

use App\Models\Department;
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
        ]);
        $this->requesterUser->assignRole('Employee');

        // Create target worker (same department as requester based on latest business rule)
        $this->targetWorker = $this->createWorker($this->department, 'Target Worker');
        $this->targetUser = User::factory()->create([
            'worker_id' => $this->targetWorker->id,
        ]);
        $this->targetUser->assignRole('Employee');

        // Create manager
        $this->managerWorker = $this->createWorker($this->department, 'Manager');
        $this->managerUser = User::factory()->create([
            'worker_id' => $this->managerWorker->id,
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
        // Same department swaps are auto-executed after target acceptance
        $this->assertEquals('executed', $swap->status);
        $this->assertFalse($swap->requires_manager_approval);
    }

    /** @test */
    public function cross_department_swap_request_is_rejected()
    {
        // Make target cross-department just for this scenario
        $this->targetWorker->update(['department_id' => $this->otherDepartment->id]);

        $this->actingAs($this->requesterUser)
            ->post(route('employee.shift-swaps.store'), [
                'requester_shift_id' => $this->requesterShift->id,
                'target_worker_id' => $this->targetWorker->id,
                'target_shift_id' => $this->targetShift->id,
                'swap_type' => 'single_date',
                'swap_date' => Carbon::parse($this->requesterShift->effective_from)->format('Y-m-d'),
                'reason' => 'Need swap',
            ]);

        $this->assertDatabaseMissing('shift_swap_requests', [
            'requester_id' => $this->requesterWorker->id,
            'target_worker_id' => $this->targetWorker->id,
        ]);
    }

    /** @test */
    public function target_acceptance_auto_executes_swap_and_creates_overrides()
    {
        // Create and accept swap (same department: auto-executes)
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

        // Verify swap was auto-executed after target acceptance
        $this->assertEquals('executed', $swap->status);
        $this->assertNotNull($swap->executed_at);
        $this->assertNull($swap->manager_approved_at);

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
            'action' => 'executed',
        ]);

        // Should have 3 audit log entries (created, accepted, executed)
        $auditCount = \App\Models\ShiftSwapAuditLog::where('shift_swap_request_id', $swap->id)->count();
        $this->assertEquals(3, $auditCount);
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
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'department_id' => $department->id,
            'hire_date' => Carbon::now()->subYears(2)->format('Y-m-d'),
            'employment_status' => 'contract',
            'status' => 'active',
        ]);
    }
}
