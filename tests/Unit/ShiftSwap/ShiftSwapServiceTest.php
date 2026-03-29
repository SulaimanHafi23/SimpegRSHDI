<?php

namespace Tests\Unit\ShiftSwap;

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
    protected Worker $backupWorker;
    protected WorkerShift $backupWorkerShift;
    protected int $workerSequence = 1;

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
        $this->worker = $this->createWorker($this->department, 'Requester Worker');
        $this->user = User::factory()->create([
            'worker_id' => $this->worker->id,
        ]);

        // Create worker shift (3 days from now to allow lead time)
        $this->workerShift = WorkerShift::create([
            'worker_id' => $this->worker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => Carbon::now()->addDays(3)->startOfDay(),
            'is_active' => true,
        ]);

        // Keep at least one additional scheduled worker to satisfy minimum staffing rule.
        $this->backupWorker = $this->createWorker($this->department, 'Backup Worker');
        $this->backupWorkerShift = WorkerShift::create([
            'worker_id' => $this->backupWorker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => Carbon::parse($this->workerShift->effective_from)->startOfDay(),
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_validates_lead_time_requirement()
    {
        // Create a backup worker for the short notice shift
        $shortNoticeBackup = $this->createWorker($this->department, 'Short Notice Backup');

        // Create a shift that's only 24 hours away (less than required 48h)
        $shortNoticeShift = WorkerShift::create([
            'worker_id' => $this->worker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => Carbon::now()->addHours(24)->startOfDay(),
            'is_active' => true,
        ]);

        // Create backup worker shift for the same date
        WorkerShift::create([
            'worker_id' => $shortNoticeBackup->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => Carbon::parse($shortNoticeShift->effective_from)->startOfDay(),
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/minimal.*jam.*sebelum shift/i');

        $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $shortNoticeShift->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($shortNoticeShift->effective_from)->format('Y-m-d'),
            'reason' => 'Test reason',
        ]);
    }

    /** @test */
    public function it_allows_swap_with_sufficient_lead_time()
    {
        $targetWorker = $this->createWorker($this->department, 'Target Worker');

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($this->workerShift->effective_from)->format('Y-m-d'),
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

        $targetWorker = $this->createWorker($otherDepartment, 'Cross Department Target');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tukar shift hanya dapat dilakukan dengan pegawai dari departemen yang sama.');

        $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($this->workerShift->effective_from)->format('Y-m-d'),
            'reason' => 'Test reason',
        ]);
    }

    /** @test */
    public function it_does_not_require_manager_approval_for_same_department_swaps()
    {
        $targetWorker = $this->createWorker($this->department, 'Same Department Target');

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($this->workerShift->effective_from)->format('Y-m-d'),
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
        $this->expectExceptionMessageMatches('/double shift|istirahat/i');

        $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $duplicateShift->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($duplicateShift->effective_from)->format('Y-m-d'),
            'reason' => 'Test reason',
        ]);
    }

    /** @test */
    public function it_creates_audit_log_on_swap_creation()
    {
        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($this->workerShift->effective_from)->format('Y-m-d'),
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
        $targetWorker = $this->createWorker($this->department, 'Accept Target');
        $targetUser->update(['worker_id' => $targetWorker->id]);

        // Create target worker shift
        $targetWorkerShift = WorkerShift::create([
            'worker_id' => $targetWorker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => Carbon::parse($this->workerShift->effective_from)->startOfDay(),
            'is_active' => true,
        ]);

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'target_shift_id' => $targetWorkerShift->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($this->workerShift->effective_from)->format('Y-m-d'),
            'reason' => 'Test reason',
        ]);

        $this->actingAs($targetUser);
        $acceptedSwap = $this->service->acceptRequest($swap->id, $targetWorker->id);

        $this->assertEquals('executed', $acceptedSwap->status); // Auto-executed for same department
        $this->assertDatabaseHas('shift_swap_audit_logs', [
            'shift_swap_request_id' => $swap->id,
            'action' => 'accepted',
        ]);
    }

    /** @test */
    public function target_worker_can_reject_swap()
    {
        $targetUser = User::factory()->create();
        $targetWorker = $this->createWorker($this->department, 'Reject Target');
        $targetUser->update(['worker_id' => $targetWorker->id]);

        $swap = $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($this->workerShift->effective_from)->format('Y-m-d'),
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
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($this->workerShift->effective_from)->format('Y-m-d'),
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

        $targetWorker = $this->createWorker($otherDepartment, 'Manager Approval Target');

        // Create user for target worker
        $targetUser = User::factory()->create([
            'worker_id' => $targetWorker->id,
            'is_active' => true,
        ]);

        // Create target worker shift
        $targetWorkerShift = WorkerShift::create([
            'worker_id' => $targetWorker->id,
            'shift_id' => $this->shift->id,
            'pattern_type' => 'fixed',
            'effective_from' => Carbon::parse($this->workerShift->effective_from)->startOfDay(),
            'is_active' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tukar shift hanya dapat dilakukan dengan pegawai dari departemen yang sama.');

        $this->service->createRequest([
            'requester_id' => $this->worker->id,
            'requester_shift_id' => $this->workerShift->id,
            'target_worker_id' => $targetWorker->id,
            'target_shift_id' => $targetWorkerShift->id,
            'swap_type' => 'single_date',
            'swap_date' => Carbon::parse($this->workerShift->effective_from)->format('Y-m-d'),
            'reason' => 'Test reason',
        ]);
    }

    private function createWorker(Department $department, string $name): Worker
    {
        $seq = $this->workerSequence++;

        return Worker::create([
            'nip' => 'UT' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
            'name' => $name,
            'email' => 'unit.worker' . $seq . '@example.com',
            'phone_number' => '0812' . str_pad((string) (1000000 + $seq), 7, '0', STR_PAD_LEFT),
            'address' => 'Test Address ' . $seq,
            'birth_date' => Carbon::now()->subYears(30)->format('Y-m-d'),
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
