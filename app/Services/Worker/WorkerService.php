<?php

namespace App\Services\Worker;

use App\DTOs\WorkerDTO;
use App\DTOs\UserDTO;
use App\Models\AuditLog;
use App\Models\SalaryComponent;
use App\Models\Worker as WorkerModel;
use App\Models\WorkerSalaryComponent;
use App\Models\User;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use App\Repositories\Contracts\Worker\WorkerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class WorkerService
{
    public function __construct(
        protected WorkerRepositoryInterface $workerRepository,
        protected UserRepositoryInterface $userRepository,
        protected WorkerEmploymentEligibilityService $eligibilityService,
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->workerRepository->getAll($filters);
    }

    public function getAllActive()
    {
        return $this->workerRepository->getAllActive();
    }

    public function getById(string $id)
    {
        return $this->workerRepository->getById($id);
    }

    /**
     * Alias for getById() for backward compatibility
     */
    public function findById(string $id)
    {
        return $this->getById($id);
    }

    public function getByNip(string $nip)
    {
        return $this->workerRepository->getByNip($nip);
    }

    public function getByDepartment(string $departmentId)
    {
        return $this->workerRepository->getByDepartment($departmentId);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            // Check if NIP already exists
            if ($this->workerRepository->getByNip($data['nip'])) {
                throw new \Exception('NIP already exists.');
            }

            // Check if email already exists
            if ($this->workerRepository->getByEmail($data['email']) || $this->userRepository->getByEmail($data['email'])) {
                throw new \Exception('Email already exists.');
            }

            // Handle photo upload
            if (isset($data['photo'])) {
                $data['photo_url'] = $this->savePhoto($data['photo'], $data['nip']);
            }

            $data = $this->normalizeWorkerPayload($data);

            $workerDTO = WorkerDTO::fromRequest($data);
            $worker = $this->workerRepository->create($workerDTO);

            // Sync default salary components based on payroll category.
            if (!empty($data['payroll_category'])) {
                $this->syncDefaultSalaryComponents(
                    $worker->id,
                    (string) $data['payroll_category'],
                    'auto_category_sync'
                );
            }

            // Create user account if requested
            if ($data['create_user_account'] ?? false) {
                $userDTO = UserDTO::fromRequest([
                    'worker_id' => $worker->id,
                    'email' => $worker->email,
                    'username' => $data['username'] ?? $worker->nip,
                    'password' => $data['password'] ?? \Illuminate\Support\Str::random(16),
                    'is_active' => true,
                ]);

                // Avoid creating duplicate user for the worker if one already exists
                if ($this->userRepository->getByWorkerId($worker->id)) {
                    // Option: skip creation and optionally sync roles
                    if (isset($data['roles'])) {
                        $existingUser = $this->userRepository->getByWorkerId($worker->id);
                        $this->userRepository->assignRoles($existingUser->id, $data['roles']);
                    }
                } else {
                        // Hash password before creating user since we're bypassing UserService here
                        $passwordPlain = $userDTO->password ?? \Illuminate\Support\Str::random(16);
                        $userDTOHashed = UserDTO::fromRequest([
                            'worker_id' => $userDTO->worker_id,
                            'email' => $userDTO->email,
                            'username' => $userDTO->username,
                            'password' => Hash::make($passwordPlain),
                            'is_active' => $userDTO->is_active,
                        ]);

                        $user = $this->userRepository->create($userDTOHashed);
                    // Assign default role
                    if (isset($data['roles'])) {
                        $this->userRepository->assignRoles($user->id, $data['roles']);
                    }
                }
            }

            DB::commit();
            return $this->workerRepository->getById($worker->id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data)
    {
        DB::beginTransaction();
        try {
            $worker = $this->workerRepository->getById($id);

            if (!empty($data['email']) && $data['email'] !== $worker->email) {
                $existingWorker = $this->workerRepository->getByEmail($data['email']);
                $existingUser = User::where('email', $data['email'])
                    ->where('worker_id', '!=', $worker->id)
                    ->first();

                if (($existingWorker && $existingWorker->id !== $worker->id) || $existingUser) {
                    throw new \Exception('Email already exists.');
                }
            }

            // Handle photo upload
            if (isset($data['photo'])) {
                // Delete old photo
                if ($worker->photo_url && Storage::exists($worker->photo_url)) {
                    Storage::delete($worker->photo_url);
                }
                $data['photo_url'] = $this->savePhoto($data['photo'], $worker->nip);
            }

            $data = $this->normalizeWorkerPayload($data, $worker);
            $data = $this->sanitizeUpdatePayload($data);

            $persistedSnapshot = [
                'nip' => $worker->nip,
                'name' => $worker->name,
                'email' => $worker->email,
                'phone_number' => $worker->phone_number,
                'address' => $worker->address,
                'birth_date' => optional($worker->birth_date)->format('Y-m-d'),
                'birth_place' => $worker->birth_place,
                'gender_id' => $worker->gender_id,
                'religion_id' => $worker->religion_id,
                'department_id' => $worker->department_id,
                'hire_date' => optional($worker->hire_date)->format('Y-m-d'),
                'resign_date' => optional($worker->resign_date)->format('Y-m-d'),
                'employment_status' => $worker->employment_status,
                'payroll_category' => $worker->payroll_category,
                'payroll_payment_type' => $worker->payroll_payment_type,
                'base_salary' => $worker->base_salary,
                'rank' => $worker->rank,
                'rank_level' => $worker->rank_level,
                'weekly_work_hours' => $worker->weekly_work_hours,
                'outsourced_vendor' => $worker->outsourced_vendor,
                'outsourced_contract_start' => optional($worker->outsourced_contract_start)->format('Y-m-d'),
                'outsourced_contract_end' => optional($worker->outsourced_contract_end)->format('Y-m-d'),
                'status' => $worker->status,
                'photo_url' => $worker->photo_url,
            ];

            $data = array_merge($persistedSnapshot, $data);

            $dto = WorkerDTO::fromRequest($data);
            $updated = $this->workerRepository->update($id, $dto);

            if (!empty($data['email']) && $worker->user && $worker->user->email !== $updated->email) {
                $worker->user->update(['email' => $updated->email]);
            }

            // Sync defaults when category changed or explicitly requested.
            if (!empty($data['payroll_category'])) {
                $shouldSync = !empty($data['auto_sync_salary_components']);

                if (!$shouldSync) {
                    $originalCategory = (string) ($worker->payroll_category ?? '');
                    $newCategory = (string) $data['payroll_category'];
                    $shouldSync = $originalCategory !== $newCategory;
                }

                if ($shouldSync) {
                    $this->syncDefaultSalaryComponents(
                        $id,
                        (string) $data['payroll_category'],
                        'auto_category_sync'
                    );
                }
            }

            DB::commit();
            return $updated;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        DB::beginTransaction();
        try {
            $worker = $this->workerRepository->getById($id);

            // Delete photo
            if ($worker->photo_url && Storage::exists($worker->photo_url)) {
                Storage::delete($worker->photo_url);
            }

            // Delete user account
            if ($worker->user) {
                $this->userRepository->delete($worker->user->id);
            }

            $result = $this->workerRepository->delete($id);

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function resign(string $id, string $resignDate)
    {
        DB::beginTransaction();
        try {
            $worker = $this->workerRepository->resign($id, $resignDate);

            // Deactivate user account
            if ($worker->user) {
                $this->userRepository->deactivate($worker->user->id);
            }

            DB::commit();
            return $worker;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function savePhoto($photo, string $nip): string
    {
        $ext = strtolower($photo->getClientOriginalExtension() ?? 'jpg');
        $filename = sprintf('%s_photo_%s.%s', $nip, now()->format('YmdHis'), $ext);

        // Try to process with Intervention Image if available; otherwise fallback to storing original file
        try {
            if (class_exists('\\Intervention\\Image\\ImageManagerStatic')) {
                $img = Image::make($photo->getRealPath());
                $img->orientate();

                // Resize if wider than 1200px, keep aspect ratio
                if ($img->width() > 1200) {
                    $img->resize(1200, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                // Encode with reasonable quality (75)
                $encoded = (string) $img->encode($ext, 75);

                $path = 'worker-photos/' . $filename;
                Storage::disk('public')->put($path, $encoded);

                return $path;
            }
        } catch (\Throwable $e) {
            // swallow and fallback
        }

        // Fallback: store original uploaded file
        return $photo->storeAs('worker-photos', $filename, 'public');
    }

    public function syncDefaultSalaryComponents(string $workerId, string $payrollCategory, string $source = 'auto_category_sync'): void
    {
        $worker = WorkerModel::query()->find($workerId);

        $oldAssignments = WorkerSalaryComponent::query()
            ->with('salaryComponent')
            ->where('worker_id', $workerId)
            ->where('is_active', true)
            ->get()
            ->map(fn (WorkerSalaryComponent $assignment) => [
                'code' => $assignment->salaryComponent?->code,
                'calculation_type' => $assignment->calculation_type,
                'amount' => (float) $assignment->amount,
            ])
            ->filter(fn (array $assignment) => !empty($assignment['code']))
            ->values()
            ->all();

        $components = SalaryComponent::query()->active()->get()->keyBy('code');

        WorkerSalaryComponent::query()
            ->where('worker_id', $workerId)
            ->update(['is_active' => false]);

        if ($payrollCategory === 'outsourced') {
            return;
        }

        $assignments = $this->defaultAssignmentsByPayrollCategory($payrollCategory);
        foreach ($assignments as $code => $config) {
            $component = $components->get($code);
            if (!$component) {
                continue;
            }

            WorkerSalaryComponent::query()->updateOrCreate(
                [
                    'worker_id' => $workerId,
                    'salary_component_id' => $component->id,
                ],
                [
                    'calculation_type' => $config['calculation_type'],
                    'amount' => $config['amount'],
                    'is_active' => true,
                    'notes' => 'Auto synced from payroll category change: ' . $payrollCategory,
                ]
            );
        }

        $newAssignments = WorkerSalaryComponent::query()
            ->with('salaryComponent')
            ->where('worker_id', $workerId)
            ->where('is_active', true)
            ->get()
            ->map(fn (WorkerSalaryComponent $assignment) => [
                'code' => $assignment->salaryComponent?->code,
                'calculation_type' => $assignment->calculation_type,
                'amount' => (float) $assignment->amount,
            ])
            ->filter(fn (array $assignment) => !empty($assignment['code']))
            ->values()
            ->all();

        if ($worker && ($oldAssignments !== $newAssignments)) {
            AuditLog::log(
                action: 'worker_payroll_components_synced',
                description: 'Sinkronisasi komponen payroll otomatis untuk kategori ' . $payrollCategory,
                auditable: $worker,
                oldValues: ['salary_components' => $oldAssignments],
                newValues: [
                    'payroll_category' => $payrollCategory,
                    'salary_components' => $newAssignments,
                    'source' => $source,
                ],
            );
        }
    }

    private function defaultAssignmentsByPayrollCategory(string $payrollCategory): array
    {
        return match ($payrollCategory) {
            'asn' => [
                'ALLOWANCE_POSITION' => ['calculation_type' => 'percentage', 'amount' => 7],
                'ALLOWANCE_HEALTH' => ['calculation_type' => 'fixed', 'amount' => 225000],
                'TAX_PPH21' => ['calculation_type' => 'percentage', 'amount' => 5],
                'BPJS_KESEHATAN' => ['calculation_type' => 'percentage', 'amount' => 1],
                'BPJS_KETENAGAKERJAAN' => ['calculation_type' => 'percentage', 'amount' => 2],
                'LATE_DEDUCTION' => ['calculation_type' => 'fixed', 'amount' => 0],
            ],

            'pppk' => [
                'ALLOWANCE_POSITION' => ['calculation_type' => 'percentage', 'amount' => 4],
                'ALLOWANCE_HEALTH' => ['calculation_type' => 'fixed', 'amount' => 150000],
                'ALLOWANCE_MEAL' => ['calculation_type' => 'percentage', 'amount' => 4],
                'OVERTIME' => ['calculation_type' => 'fixed', 'amount' => 0],
                'TAX_PPH21' => ['calculation_type' => 'percentage', 'amount' => 5],
                'BPJS_KESEHATAN' => ['calculation_type' => 'percentage', 'amount' => 1],
                'BPJS_KETENAGAKERJAAN' => ['calculation_type' => 'percentage', 'amount' => 2],
                'ABSENT_DEDUCTION' => ['calculation_type' => 'fixed', 'amount' => 0],
                'LATE_DEDUCTION' => ['calculation_type' => 'fixed', 'amount' => 0],
            ],

            'pppk_paruh_waktu' => [
                'ALLOWANCE_MEAL' => ['calculation_type' => 'percentage', 'amount' => 2],
                'OVERTIME' => ['calculation_type' => 'fixed', 'amount' => 0],
                'ABSENT_DEDUCTION' => ['calculation_type' => 'fixed', 'amount' => 0],
                'LATE_DEDUCTION' => ['calculation_type' => 'fixed', 'amount' => 0],
                'BPJS_KESEHATAN' => ['calculation_type' => 'percentage', 'amount' => 1],
            ],

            default => [
                'ALLOWANCE_TRANSPORT' => ['calculation_type' => 'percentage', 'amount' => 8],
                'ALLOWANCE_MEAL' => ['calculation_type' => 'percentage', 'amount' => 5],
                'ALLOWANCE_POSITION' => ['calculation_type' => 'percentage', 'amount' => 3],
                'OVERTIME' => ['calculation_type' => 'fixed', 'amount' => 0],
                'ABSENT_DEDUCTION' => ['calculation_type' => 'fixed', 'amount' => 0],
                'LATE_DEDUCTION' => ['calculation_type' => 'fixed', 'amount' => 0],
                'TAX_PPH21' => ['calculation_type' => 'percentage', 'amount' => 5],
                'BPJS_KESEHATAN' => ['calculation_type' => 'percentage', 'amount' => 1],
            ],
        };
    }

    private function normalizeWorkerPayload(array $data, ?object $existingWorker = null): array
    {
        $category = $this->eligibilityService->normalizeCategory(
            $data['payroll_category'] ?? ($existingWorker->payroll_category ?? null)
        );

        $data['payroll_category'] = $category;

        if ($category === WorkerEmploymentEligibilityService::CATEGORY_OUTSOURCED) {
            $data['payroll_payment_type'] = $data['payroll_payment_type'] ?? ($existingWorker->payroll_payment_type ?? 'vendor_invoice');
            $data['payroll_payment_type'] = $data['payroll_payment_type'] === 'individual' ? 'individual' : 'vendor_invoice';
        } else {
            $data['payroll_payment_type'] = 'individual';
            $data['outsourced_vendor'] = null;
            $data['outsourced_contract_start'] = null;
            $data['outsourced_contract_end'] = null;
        }

        if ($category === WorkerEmploymentEligibilityService::CATEGORY_PPPK_PART_TIME) {
            if (empty($data['weekly_work_hours']) && empty($existingWorker?->weekly_work_hours)) {
                $data['weekly_work_hours'] = 20;
            }
        } else {
            $data['weekly_work_hours'] = null;
        }

        return $data;
    }

    private function sanitizeUpdatePayload(array $data): array
    {
        $nullableFields = [
            'photo_url',
            'resign_date',
            'rank',
            'rank_level',
            'weekly_work_hours',
            'outsourced_vendor',
            'outsourced_contract_start',
            'outsourced_contract_end',
        ];

        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return array_filter($data, function ($value, $key) use ($nullableFields) {
            if (in_array($key, $nullableFields, true)) {
                return true;
            }

            return $value !== '' && $value !== null && $value !== [];
        }, ARRAY_FILTER_USE_BOTH);
    }
}
