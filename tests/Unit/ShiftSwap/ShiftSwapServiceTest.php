<?php

namespace Tests\Unit\ShiftSwap;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerShift;
use App\Services\ShiftSwap\ShiftSwapService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftSwapServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ShiftSwapService $service;
    protected User $user;
    protected Worker $worker;
    protected Department $department;
    protected Shift $shift;
    protected WorkerShift $workerShift;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new ShiftSwapService();

        // Create department
        $this->department = Department::create([
            'name' => 'Test Department',
            'code' => 'TEST',
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

        // Create user and worker
        $this->user = User::factory()->create();
        $this->worker = Worker::create([
            'user_id' => $this->user->id,
            'department_id' => $this->department->id,
            'name' => 'Test Worker',
            'nip' => '123456',
            'hire_date' => Carbon::now()->subYears(2),
            'is_active' => true,
        ]);

        // Create worker shift (3 days from now to allow lead time)
        $this->workerShift = WorkerShift::create([
            'worker_id' => $this->worker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => Carbon::now()->addDays(3),
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_validates_lead_time_requirement()
    {
        // Create a shift that's only 24 hours away (less than required 48h)
        $shortNoticeShift = WorkerShift::create([
            'worker_id' => $this->worker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => Carbon::now()->addHours(24),
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/minimal.*jam sebelum shift/i');

        $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $shortNoticeShift->id,
            'reason' => 'Test reason',
        ]);
    }

    /** @test */
    public function it_allows_swap_with_sufficient_lead_time()
    {
        $targetWorker = Worker::create([
            'user_id' => User::factory()->create()->id,
            'department_id' => $this->department->id,
            'name' => 'Target Worker',
            'nip' => '789012',
            'hire_date' => Carbon::now()->subYears(1),
            'is_active' => true,
        ]);

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'reason' => 'Test reason',
        ]);

        $this->assertInstanceOf(ShiftSwapRequest::class, $swap);
        $this->assertEquals('pending', $swap->status);
        $this->assertDatabaseHas('shift_swap_requests', [
            'id' => $swap->id,
            'requester_id' => $this->worker->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_requires_manager_approval_for_cross_department_swaps()
    {
        // Create another department and worker
        $otherDepartment = Department::create([
            'name' => 'Other Department',
            'code' => 'OTHER',
        ]);

        $targetWorker = Worker::create([
            'user_id' => User::factory()->create()->id,
            'department_id' => $otherDepartment->id,
            'name' => 'Target Worker',
            'nip' => '789012',
            'hire_date' => Carbon::now()->subYears(1),
            'is_active' => true,
        ]);

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'reason' => 'Test reason',
        ]);

        $this->assertTrue($swap->requires_manager_approval);
    }

    /** @test */
    public function it_does_not_require_manager_approval_for_same_department_swaps()
    {
        $targetWorker = Worker::create([
            'user_id' => User::factory()->create()->id,
            'department_id' => $this->department->id, // Same department
            'name' => 'Target Worker',
            'nip' => '789012',
            'hire_date' => Carbon::now()->subYears(1),
            'is_active' => true,
        ]);

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'reason' => 'Test reason',
        ]);

        $this->assertFalse($swap->requires_manager_approval);
    }

    /** @test */
    public function it_validates_double_shift_prevention()
    {
        $shiftDate = Carbon::now()->addDays(3);

        // Create an existing shift on the same date
        $existingShift = WorkerShift::create([
            'worker_id' => $this->worker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => $shiftDate,
            'is_active' => true,
        ]);

        // Try to create another shift on the same date
        $duplicateShift = WorkerShift::create([
            'worker_id' => $this->worker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => $shiftDate,
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/double shift/i');

        $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $duplicateShift->id,
            'reason' => 'Test reason',
        ]);
    }

    /** @test */
    public function it_creates_audit_log_on_swap_creation()
    {
        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'reason' => 'Test reason',
        ]);

        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'created',
            'new_status' => 'pending',
        ]);
    }

    /** @test */
    public function target_worker_can_accept_swap()
    {
        $targetUser = User::factory()->create();
        $targetWorker = Worker::create([
            'user_id' => $targetUser->id,
            'department_id' => $this->department->id,
            'name' => 'Target Worker',
            'nip' => '789012',
            'hire_date' => Carbon::now()->subYears(1),
            'is_active' => true,
        ]);

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'reason' => 'Test reason',
        ]);

        $this->actingAs($targetUser);
        $acceptedSwap = $this->service->acceptRequest($swap->id, $targetWorker->id);

        $this->assertEquals('accepted', $acceptedSwap->status);
        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'accepted',
        ]);
    }

    /** @test */
    public function target_worker_can_reject_swap()
    {
        $targetUser = User::factory()->create();
        $targetWorker = Worker::create([
            'user_id' => $targetUser->id,
            'department_id' => $this->department->id,
            'name' => 'Target Worker',
            'nip' => '789012',
            'hire_date' => Carbon::now()->subYears(1),
            'is_active' => true,
        ]);

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'reason' => 'Test reason',
        ]);

        $this->actingAs($targetUser);
        $rejectedSwap = $this->service->rejectRequest($swap->id, $targetWorker->id, 'Not available');

        $this->assertEquals('rejected', $rejectedSwap->status);
        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'rejected',
        ]);
    }

    /** @test */
    public function requester_can_cancel_swap()
    {
        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'reason' => 'Test reason',
        ]);

        $cancelledSwap = $this->service->cancelRequest($swap->id, $this->worker->id);

        $this->assertEquals('cancelled', $cancelledSwap->status);
        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'cancelled',
        ]);
    }

    /** @test */
    public function manager_can_approve_cross_department_swap()
    {
        $otherDepartment = Department::create([
            'name' => 'Other Department',
            'code' => 'OTHER',
        ]);

        $targetWorker = Worker::create([
            'user_id' => User::factory()->create()->id,
            'department_id' => $otherDepartment->id,
            'name' => 'Target Worker',
            'nip' => '789012',
            'hire_date' => Carbon::now()->subYears(1),
            'is_active' => true,
        ]);

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'reason' => 'Test reason',
        ]);

        // Accept by target first
        $swap->status = 'awaiting_approval';
        $swap->save();

        $managerUser = User::factory()->create();
        $this->actingAs($managerUser);

        $approvedSwap = $this->service->approveByManager($swap->id, $managerUser->id, 'Approved');

        $this->assertEquals('approved', $approvedSwap->status);
        $this->assertEquals($managerUser->id, $approvedSwap->manager_id);
        $this->assertNotNull($approvedSwap->manager_approved_at);
    }
}
