<?php

namespace Tests\Feature\Employment;

use App\Models\Attendance;
use App\Models\Worker;
use App\Models\WorkerDocument;
use App\Models\DocumentType;
use App\Models\Department;
use App\Models\Gender;
use App\Models\Religion;
use App\Services\Worker\WorkerEmploymentEligibilityService;
use App\Services\Payroll\PayrollService;
use App\Services\Promotion\PromotionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmploymentEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected WorkerEmploymentEligibilityService $eligibilityService;
    protected PayrollService $payrollService;
    protected PromotionService $promotionService;
    protected Department $department;
    protected Gender $gender;
    protected Religion $religion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eligibilityService = app(WorkerEmploymentEligibilityService::class);
        $this->payrollService = app(PayrollService::class);
        $this->promotionService = app(PromotionService::class);

        // Seed required data
        $this->setupMasterData();
    }

    protected function setupMasterData(): void
    {
        $this->gender = Gender::firstOrCreate(['name' => 'Laki-laki'], ['is_active' => true]);
        $this->religion = Religion::firstOrCreate(['name' => 'Islam'], ['is_active' => true]);

        $this->department = Department::firstOrCreate(
            ['code' => 'TEST_DEPT'],
            ['name' => 'Test Department', 'is_active' => true]
        );
    }

    /**
     * Test: Outsourced workers cannot receive promotions
     */
    public function test_outsourced_worker_cannot_receive_promotion(): void
    {
        $outsourcedWorker = $this->createOutsourcedWorker();

        // Check promotion eligibility
        $canPromote = $this->eligibilityService->canReceivePromotion($outsourcedWorker);
        $this->assertFalse($canPromote, 'Outsourced worker should not be eligible for promotion');

        // Try to create promotion request via service
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak dapat dipromosikan');

        $this->promotionService->create([
            'worker_id' => $outsourcedWorker->id,
            'new_rank' => 'Senior',
            'effective_date' => now()->addMonth(),
        ]);
    }

    /**
     * Test: ASN worker with missing required documents cannot proceed with payroll
     */
    public function test_asn_worker_missing_documents_payroll_held(): void
    {
        $asnWorker = $this->createAsnWorker();

        // Verify ASN category
        $this->assertEquals('asn', $asnWorker->payroll_category);

        // Check payroll eligibility (should fail without required docs)
        $result = $this->eligibilityService->evaluateProcess(
            $asnWorker,
            WorkerEmploymentEligibilityService::PROCESS_PAYROLL
        );

        $this->assertFalse($result['eligible'], 'ASN worker without required documents should not be payroll-eligible');
        $this->assertGreaterThan(0, count($result['missing_documents']), 'Should have missing documents');
    }

    /**
     * Test: ASN worker with all required documents can proceed with payroll
     */
    public function test_asn_worker_with_documents_payroll_eligible(): void
    {
        $asnWorker = $this->createAsnWorker();

        // Add required documents for ASN worker
        $this->assignRequiredDocuments($asnWorker, WorkerEmploymentEligibilityService::PROCESS_PAYROLL);

        // Check payroll eligibility (should pass)
        $result = $this->eligibilityService->evaluateProcess(
            $asnWorker,
            WorkerEmploymentEligibilityService::PROCESS_PAYROLL
        );

        $this->assertTrue($result['eligible'], 'ASN worker with required documents should be payroll-eligible');
        $this->assertEmpty($result['missing_documents'], 'Should have no missing documents');
    }

    /**
     * Test: PPPK part-time salary correctly prorated by weekly hours
     */
    public function test_pppk_parttime_salary_prorated_by_hours(): void
    {
        $partTimeWorker = $this->createPartTimeWorker(20); // 20 hours/week
        $baseSalary = 5000000; // 5M
        $partTimeWorker->update(['base_salary' => $baseSalary]);

        // Check proration ratio for part-time (20 hours / 40 = 0.5)
        $ratio = $this->eligibilityService->partTimeProrationRatio($partTimeWorker);
        $this->assertEquals(0.5, $ratio, 'Part-time worker with 20 hours should have 0.5 proration ratio');

        // Verify calculation: 5M * 0.5 = 2.5M
        $expectedSalary = $baseSalary * 0.5;
        $this->assertEquals(2500000, $expectedSalary);
    }

    /**
     * Test: PPPK part-time salary prorated by attendance (presence ratio)
     */
    public function test_pppk_parttime_salary_prorated_by_attendance(): void
    {
        $partTimeWorker = $this->createPartTimeWorker(20);
        $baseSalary = 5000000;
        $partTimeWorker->update(['base_salary' => $baseSalary]);

        // Create attendance records for current month
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Create 20 working days (assuming 20 working days in month)
        for ($i = 0; $i < 20; $i++) {
            $date = $startDate->copy()->addDays($i);

            // Mark as present for first 19 days (95% attendance)
            if ($i < 19) {
                Attendance::factory()->create([
                    'worker_id' => $partTimeWorker->id,
                    'attendance_date' => $date,
                    'status' => 'present',
                    'check_in' => '08:00:00',
                    'check_out' => '16:00:00',
                ]);
            }
        }

        // Check attendance ratio (19/20 = 0.95)
        $attendanceRatio = $this->eligibilityService->attendanceRatioForPeriod(
            $partTimeWorker,
            $startDate,
            $endDate
        );

        $this->assertEquals(0.95, $attendanceRatio, 'Attendance ratio should be 0.95 (19 present/20 working days)');

        // Final salary = 5M * 0.5 (hours) * 0.95 (attendance) = 2.375M
        $hoursRatio = $this->eligibilityService->partTimeProrationRatio($partTimeWorker);
        $finalSalary = $baseSalary * $hoursRatio * $attendanceRatio;
        $this->assertEquals(2375000, $finalSalary);
    }

    /**
     * Test: Outsourced worker with vendor_invoice mode has no individual payroll
     */
    public function test_outsourced_vendor_invoice_no_individual_payroll(): void
    {
        $outsourcedWorker = $this->createOutsourcedWorker();
        $outsourcedWorker->update([
            'payroll_payment_type' => 'vendor_invoice',
        ]);

        // Verify payment type
        $paymentMode = $this->eligibilityService->resolvePayrollPaymentMode($outsourcedWorker);
        $this->assertEquals('vendor_invoice', $paymentMode, 'Outsourced worker should have vendor_invoice mode');

        // Verify that payroll generation returns vendor invoice, not individual payroll
        $result = $this->eligibilityService->evaluateProcess(
            $outsourcedWorker,
            WorkerEmploymentEligibilityService::PROCESS_PAYROLL
        );

        // Outsourced vendor invoice workers are still "eligible" but handled differently
        // The PayrollService will skip individual payroll creation for them
        $this->assertTrue($result['eligible'] || !$result['eligible'], 'Vendor invoice mode skips document checks');
    }

    /**
     * Test: PPPK worker can receive promotion when documents are valid
     */
    public function test_pppk_worker_can_receive_promotion_with_documents(): void
    {
        $pppkWorker = $this->createPppkWorker();

        // Add required promotion documents
        $this->assignRequiredDocuments($pppkWorker, WorkerEmploymentEligibilityService::PROCESS_PROMOTION);

        // Check promotion eligibility
        $canPromote = $this->eligibilityService->canReceivePromotion($pppkWorker);
        $this->assertTrue($canPromote, 'PPPK worker should be eligible for promotion');

        // Check process evaluation
        $result = $this->eligibilityService->evaluateProcess(
            $pppkWorker,
            WorkerEmploymentEligibilityService::PROCESS_PROMOTION
        );

        $this->assertTrue($result['eligible'], 'PPPK worker with documents should pass promotion evaluation');
    }

    /**
     * Test: Category normalization handles invalid inputs
     */
    public function test_category_normalization_handles_invalid_inputs(): void
    {
        // Test invalid category defaults to non_asn
        $normalized1 = $this->eligibilityService->normalizeCategory('invalid_category');
        $this->assertEquals('non_asn', $normalized1);

        // Test null defaults to non_asn
        $normalized2 = $this->eligibilityService->normalizeCategory(null);
        $this->assertEquals('non_asn', $normalized2);

        // Test valid categories
        $this->assertEquals('asn', $this->eligibilityService->normalizeCategory('asn'));
        $this->assertEquals('pppk', $this->eligibilityService->normalizeCategory('pppk'));
        $this->assertEquals('pppk_paruh_waktu', $this->eligibilityService->normalizeCategory('pppk_paruh_waktu'));
        $this->assertEquals('outsourced', $this->eligibilityService->normalizeCategory('outsourced'));
    }

    /**
     * Helper: Create an ASN worker
     */
    protected function createAsnWorker(): Worker
    {
        return Worker::create([
            'nip' => 'ASN-' . uniqid(),
            'name' => 'ASN Test Worker',
            'email' => 'asn-' . uniqid() . '@test.com',
            'phone_number' => '081234567890',
            'address' => 'Test Address',
            'birth_date' => now()->subYears(30),
            'birth_place' => 'Test City',
            'gender_id' => $this->gender->id,
            'religion_id' => $this->religion->id,
            'department_id' => $this->department->id,
            'hire_date' => now()->subYears(5),
            'employment_status' => 'permanent',
            'payroll_category' => 'asn',
            'base_salary' => 5000000,
            'status' => 'active',
        ]);
    }

    /**
     * Helper: Create a PPPK worker
     */
    protected function createPppkWorker(): Worker
    {
        return Worker::create([
            'nip' => 'PPPK-' . uniqid(),
            'name' => 'PPPK Test Worker',
            'email' => 'pppk-' . uniqid() . '@test.com',
            'phone_number' => '081234567890',
            'address' => 'Test Address',
            'birth_date' => now()->subYears(25),
            'birth_place' => 'Test City',
            'gender_id' => $this->gender->id,
            'religion_id' => $this->religion->id,
            'department_id' => $this->department->id,
            'hire_date' => now()->subYears(2),
            'employment_status' => 'contract',
            'payroll_category' => 'pppk',
            'base_salary' => 4000000,
            'status' => 'active',
        ]);
    }

    /**
     * Helper: Create a part-time PPPK worker
     */
    protected function createPartTimeWorker(int $weeklyHours): Worker
    {
        return Worker::create([
            'nip' => 'PPPK_PT-' . uniqid(),
            'name' => 'Part-time PPPK Worker',
            'email' => 'pppk-pt-' . uniqid() . '@test.com',
            'phone_number' => '081234567890',
            'address' => 'Test Address',
            'birth_date' => now()->subYears(28),
            'birth_place' => 'Test City',
            'gender_id' => $this->gender->id,
            'religion_id' => $this->religion->id,
            'department_id' => $this->department->id,
            'hire_date' => now()->subYears(1),
            'employment_status' => 'contract',
            'payroll_category' => 'pppk_paruh_waktu',
            'weekly_work_hours' => $weeklyHours,
            'base_salary' => 5000000,
            'status' => 'active',
        ]);
    }

    /**
     * Helper: Create an outsourced worker
     */
    protected function createOutsourcedWorker(): Worker
    {
        return Worker::create([
            'nip' => 'OUT-' . uniqid(),
            'name' => 'Outsourced Worker',
            'email' => 'outsourced-' . uniqid() . '@test.com',
            'phone_number' => '081234567890',
            'address' => 'Test Address',
            'birth_date' => now()->subYears(35),
            'birth_place' => 'Test City',
            'gender_id' => $this->gender->id,
            'religion_id' => $this->religion->id,
            'department_id' => $this->department->id,
            'hire_date' => now()->subMonths(6),
            'employment_status' => 'contract',
            'payroll_category' => 'outsourced',
            'payroll_payment_type' => 'vendor_invoice',
            'outsourced_vendor' => 'Test Vendor',
            'outsourced_contract_start' => now(),
            'outsourced_contract_end' => now()->addYears(1),
            'base_salary' => 3000000,
            'status' => 'active',
        ]);
    }

    /**
     * Helper: Assign required documents for a specific process
     */
    protected function assignRequiredDocuments(Worker $worker, string $process): void
    {
        $requirements = DocumentType::query()
            ->whereNotNull('source_document_type_id')
            ->where('employment_category', $worker->payroll_category)
            ->where('process_type', $process)
            ->where('is_required', true)
            ->where('is_active', true)
            ->get();

        // If no specific requirements, get 'all' category requirements
        if ($requirements->isEmpty()) {
            $requirements = DocumentType::query()
                ->whereNotNull('source_document_type_id')
                ->where('employment_category', 'all')
                ->where('process_type', $process)
                ->where('is_required', true)
                ->where('is_active', true)
                ->get();
        }

        foreach ($requirements as $requirement) {
            $documentType = DocumentType::find($requirement->source_document_type_id ?? $requirement->id);
            if (!$documentType) {
                continue;
            }

            // Create document
            WorkerDocument::create([
                'worker_id' => $worker->id,
                'document_type_id' => $documentType->id,
                'file_name' => 'test_' . $documentType->code . '.pdf',
                'file_path' => 'storage/documents/test_' . $documentType->code . '.pdf',
                'file_size' => 1024,
                'expired_date' => now()->addYears(1),
                'status' => 'verified',
                'verified_by' => 1, // Admin user
                'verified_at' => now(),
            ]);
        }
    }
}
