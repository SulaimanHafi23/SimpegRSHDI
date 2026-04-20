<?php

namespace App\Http\Controllers\WorkerShift;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Shift;
use App\Models\Worker;
use App\Models\WorkerShift;
use App\Models\WorkerShiftHistory;
use App\Traits\DepartmentFilterable;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorkerShiftController extends Controller
{
    use DepartmentFilterable;

    public function __construct() {}

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $filters = [
            'worker_id' => $request->worker_id,
            'shift_id' => $request->shift_id,
            'is_active' => $request->is_active,
            'search' => $search,
            'per_page' => $request->per_page ?? 15,
        ];

        // Get all active workers with their latest shift
        $departmentId = $this->getManagerDepartmentFilter();
        $workers = $departmentId
            ? $this->getActiveWorkersByDepartment($departmentId)
            : $this->getAllActiveWorkers();

        // Get all workers with their latest active shift
            $today = Carbon::today();

            $workersWithShifts = $workers->map(function($worker) use ($filters, $today) {
                $baseQuery = $worker->workerShifts()
                    ->with(['shift'])
                    ->when(!empty($filters['shift_id']), function($q) use ($filters) {
                        return $q->where('shift_id', $filters['shift_id']);
                    })
                    ->when($filters['is_active'] !== null && $filters['is_active'] !== '', function($q) use ($filters) {
                        return $q->where('is_active', $filters['is_active']);
                    });

                // 1) Prefer shift active today
                $latestShift = (clone $baseQuery)
                    ->where('effective_from', '<=', $today)
                    ->where(function($q) use ($today) {
                        $q->whereNull('effective_until')
                          ->orWhere('effective_until', '>=', $today);
                    })
                    ->orderBy('effective_from', 'desc')
                    ->first();

                // 2) Fallback to latest past shift
                if (!$latestShift) {
                    $latestShift = (clone $baseQuery)
                        ->where('effective_from', '<=', $today)
                        ->orderBy('effective_from', 'desc')
                        ->first();
                }

                // 3) Final fallback to latest overall
                if (!$latestShift) {
                    $latestShift = (clone $baseQuery)
                        ->orderBy('effective_from', 'desc')
                        ->first();
                }

            $allWorkerShifts = $worker->workerShifts()->select('shift_id')->get();
            $worker->hasRotation = $allWorkerShifts->count() > 1
                && $allWorkerShifts->pluck('shift_id')->unique()->count() > 1;

            $worker->latestShift = $latestShift;
            return $worker;
        });

        // Apply worker filter if specified
        if (isset($filters['worker_id']) && $filters['worker_id']) {
            $workersWithShifts = $workersWithShifts->where('id', $filters['worker_id']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = mb_strtolower($filters['search']);
            $workersWithShifts = $workersWithShifts->filter(function ($worker) use ($searchTerm) {
                $name = mb_strtolower((string) ($worker->name ?? ''));
                $employeeNumber = mb_strtolower((string) ($worker->employee_number ?? ''));

                return str_contains($name, $searchTerm) || str_contains($employeeNumber, $searchTerm);
            });
        }

        $workersWithShifts = $workersWithShifts->values();

        // Paginate manually
        $perPage = $filters['per_page'];
        $currentPage = $request->get('page', 1);
        $workersWithShifts = new \Illuminate\Pagination\LengthAwarePaginator(
            $workersWithShifts->forPage($currentPage, $perPage),
            $workersWithShifts->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $shifts = $this->getActiveShifts();
        $departments = $this->getActiveDepartments();

        return view('admin.schedules.index', compact('workersWithShifts', 'workers', 'shifts', 'departments', 'filters', 'departmentId'));
    }

    public function create()
    {
        $departmentId = $this->getManagerDepartmentFilter();
        $workers = $departmentId
            ? $this->getActiveWorkersByDepartment($departmentId)
            : $this->getAllActiveWorkers();
        $shifts = $this->getActiveShifts();

        return view('admin.schedules.create', compact('workers', 'shifts'));
    }

    public function generate()
    {
        $departmentId = $this->getManagerDepartmentFilter();
        $workers = $departmentId
            ? $this->getActiveWorkersByDepartment($departmentId)
            : $this->getAllActiveWorkers();
        $shifts = $this->getActiveShifts();

        return view('admin.schedules.generate', compact('workers', 'shifts'));
    }

    public function generateStore(Request $request)
    {
        $data = $request->validate([
            'worker_id' => 'nullable|exists:workers,id',
            'worker_ids' => 'nullable|array',
            'worker_ids.*' => 'exists:workers,id',
            'rotation_type' => 'required|in:weekly,monthly',
            'shift_sequence' => 'required|array|min:1',
            'shift_sequence.*' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'deactivate_existing' => 'nullable|boolean',
        ], [
            'rotation_type.required' => 'Tipe rotasi wajib dipilih.',
            'rotation_type.in' => 'Tipe rotasi harus weekly atau monthly.',
            'shift_sequence.required' => 'Urutan shift wajib diisi minimal satu.',
            'shift_sequence.*.required' => 'Setiap urutan shift wajib diisi.',
            'shift_sequence.*.exists' => 'Salah satu shift pada urutan tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        if (empty($data['worker_id']) && empty($data['worker_ids'])) {
            return back()->withInput()->withErrors(['worker_id' => 'Harap pilih minimal satu pegawai.']);
        }

        $workerIds = $data['worker_ids'] ?? null;
        if (empty($workerIds) && !empty($data['worker_id'])) {
            $workerIds = [$data['worker_id']];
        }

        if (empty($workerIds) || !is_array($workerIds)) {
            return back()->withInput()->withErrors(['worker_id' => 'Tidak ada pegawai yang dipilih.']);
        }

        $sequence = array_values(array_filter($data['shift_sequence']));
        if (empty($sequence)) {
            return back()->withInput()->withErrors(['shift_sequence' => 'Urutan shift wajib diisi minimal satu.']);
        }

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = !empty($data['end_date']) ? Carbon::parse($data['end_date'])->startOfDay() : null;
        $maxPeriods = 12;

        $deactivateExisting = $request->boolean('deactivate_existing');
        if ($deactivateExisting) {
            foreach ($workerIds as $workerId) {
                $this->deleteOldShifts($workerId);
            }
        }

        $created = 0;
        $periods = 0;
        $currentStart = $start->copy();
        $sequenceIndex = 0;

        while (true) {
            if ($end && $currentStart->gt($end)) {
                break;
            }

            if (!$end && $periods >= $maxPeriods) {
                break;
            }

            if ($data['rotation_type'] === 'weekly') {
                $periodEnd = $currentStart->copy()->addDays(6);
            } else {
                $periodEnd = $currentStart->copy()->endOfMonth();
            }

            if ($end && $periodEnd->gt($end)) {
                $periodEnd = $end->copy();
            }

            $shiftId = $sequence[$sequenceIndex % count($sequence)];

            foreach ($workerIds as $workerId) {
                $this->createWorkerShift([
                    'worker_id' => $workerId,
                    'shift_id' => $shiftId,
                    'start_date' => $currentStart->toDateString(),
                    'end_date' => $periodEnd->toDateString(),
                    'is_active' => true,
                    'notes' => $data['notes'] ?? 'Rotasi otomatis',
                    'skip_deactivate' => true,
                ]);
                $created++;
            }

            $periods++;
            $currentStart = $periodEnd->copy()->addDay();
            $sequenceIndex++;
        }

        if (!$end) {
            $end = $currentStart->copy()->subDay();
        }

        return redirect()
            ->route('admin.worker-shifts.index')
            ->with('success', "Rotasi berhasil dibuat: {$created} jadwal ({$periods} periode) untuk " . count($workerIds) . " pegawai.");
    }

    public function store(Request $request)
    {
        $isRotationMode = $request->boolean('generate_rotation');

        if ($isRotationMode) {
            $data = $request->validate([
                'worker_id' => 'nullable|exists:workers,id',
                'worker_ids' => 'nullable|array',
                'worker_ids.*' => 'exists:workers,id',
                'rotation_type' => 'required|in:weekly,monthly',
                'shift_sequence' => 'required|array|min:1',
                'shift_sequence.*' => 'required|exists:shifts,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'notes' => 'nullable|string',
                'deactivate_existing' => 'nullable|boolean',
            ], [
                'rotation_type.required' => 'Tipe rotasi wajib dipilih.',
                'rotation_type.in' => 'Tipe rotasi harus weekly atau monthly.',
                'shift_sequence.required' => 'Urutan shift wajib diisi minimal satu.',
                'shift_sequence.*.required' => 'Setiap urutan shift wajib diisi.',
                'shift_sequence.*.exists' => 'Salah satu shift pada urutan tidak valid.',
                'start_date.required' => 'Tanggal mulai wajib diisi.',
                'start_date.date' => 'Format tanggal mulai tidak valid.',
                'end_date.date' => 'Format tanggal selesai tidak valid.',
                'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            ]);

            if (empty($data['worker_id']) && empty($data['worker_ids'])) {
                return back()->withInput()->withErrors(['worker_id' => 'Harap pilih minimal satu pegawai.']);
            }

            $workerIds = $data['worker_ids'] ?? null;
            if (empty($workerIds) && !empty($data['worker_id'])) {
                $workerIds = [$data['worker_id']];
            }

            if (empty($workerIds) || !is_array($workerIds)) {
                return back()->withInput()->withErrors(['worker_id' => 'Tidak ada pegawai yang dipilih.']);
            }

            $sequence = array_values(array_filter($data['shift_sequence']));
            if (empty($sequence)) {
                return back()->withInput()->withErrors(['shift_sequence' => 'Urutan shift wajib diisi minimal satu.']);
            }

            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = !empty($data['end_date']) ? Carbon::parse($data['end_date'])->startOfDay() : null;
            $maxPeriods = 12;

            $deactivateExisting = $request->boolean('deactivate_existing');
            if ($deactivateExisting) {
                foreach ($workerIds as $workerId) {
                    $this->deleteOldShifts($workerId);
                }
            }

            $created = 0;
            $periods = 0;
            $currentStart = $start->copy();
            $sequenceIndex = 0;

            while (true) {
                if ($end && $currentStart->gt($end)) {
                    break;
                }

                if (!$end && $periods >= $maxPeriods) {
                    break;
                }

                if ($data['rotation_type'] === 'weekly') {
                    $periodEnd = $currentStart->copy()->addDays(6);
                } else {
                    $periodEnd = $currentStart->copy()->endOfMonth();
                }

                if ($end && $periodEnd->gt($end)) {
                    $periodEnd = $end->copy();
                }

                $shiftId = $sequence[$sequenceIndex % count($sequence)];

                foreach ($workerIds as $workerId) {
                    $this->createWorkerShift([
                        'worker_id' => $workerId,
                        'shift_id' => $shiftId,
                        'start_date' => $currentStart->toDateString(),
                        'end_date' => $periodEnd->toDateString(),
                        'is_active' => true,
                        'notes' => $data['notes'] ?? 'Rotasi otomatis',
                        'skip_deactivate' => true,
                    ]);
                    $created++;
                }

                $periods++;
                $currentStart = $periodEnd->copy()->addDay();
                $sequenceIndex++;
            }

            return redirect()
                ->route('admin.worker-shifts.index')
                ->with('success', "Rotasi berhasil dibuat: {$created} jadwal ({$periods} periode) untuk " . count($workerIds) . " pegawai.");
        }

        // Validasi manual untuk mode jadwal tetap
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'worker_id' => 'nullable|exists:workers,id',
            'worker_ids' => 'nullable|array',
            'worker_ids.*' => 'exists:workers,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ], [
            'shift_id.required' => 'Shift wajib dipilih.',
            'shift_id.exists' => 'Shift yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'worker_id.exists' => 'Pegawai yang dipilih tidak valid.',
            'worker_ids.array' => 'Format data pegawai tidak valid.',
            'worker_ids.*.exists' => 'Salah satu pegawai yang dipilih tidak valid.',
        ]);

        if (empty($data['worker_id']) && empty($data['worker_ids'])) {
            return back()->withInput()->withErrors(['worker_id' => 'Harap pilih minimal satu pegawai.']);
        }

        try {

            // Support multiple worker_ids[] or single worker_id
            $workerIds = $data['worker_ids'] ?? null;
            if (empty($workerIds) && !empty($data['worker_id'])) {
                $workerIds = [$data['worker_id']];
            }

            if (empty($workerIds) || !is_array($workerIds)) {
                throw new \Exception('Tidak ada pegawai yang dipilih.');
            }

            $created = 0;
            foreach ($workerIds as $workerId) {
                // Prepare per-worker payload
                $payload = $data;
                $payload['worker_id'] = $workerId;
                $payload['is_active'] = $data['is_active'] ?? true;
                // Remove worker_ids to avoid confusion
                unset($payload['worker_ids']);

                $this->createWorkerShift($payload);
                $created++;
            }

            return redirect()
                ->route('admin.worker-shifts.index')
                ->with('success', "Jadwal shift berhasil ditambahkan untuk {$created} pegawai");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $workerShift = WorkerShift::with(['worker.department', 'shift'])->findOrFail($id);
        $shiftHistories = $this->getShiftHistories($workerShift->worker_id);
        $rotationShifts = $this->getByWorkerId($workerShift->worker_id)
            ->sortBy('effective_from')
            ->values();

        $isRotating = $rotationShifts->count() > 1
            && $rotationShifts->pluck('shift_id')->unique()->count() > 1;

        return view('admin.schedules.show', compact('workerShift', 'shiftHistories', 'rotationShifts', 'isRotating'));
    }

    public function edit(string $id)
    {
        $workerShift = WorkerShift::with(['worker.department', 'shift'])->findOrFail($id);
        $workers = $this->getAllActiveWorkers();
        $shifts = $this->getActiveShifts();

        return view('admin.schedules.edit', compact('workerShift', 'workers', 'shifts'));
    }

    public function update(Request $request, string $id)
    {
        $isRotationMode = $request->boolean('generate_rotation');

        if ($isRotationMode) {
            $data = $request->validate([
                'worker_id' => 'required|exists:workers,id',
                'rotation_type' => 'required|in:weekly,monthly',
                'shift_sequence' => 'required|array|min:1',
                'shift_sequence.*' => 'required|exists:shifts,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'notes' => 'nullable|string',
                'deactivate_existing' => 'nullable|boolean',
            ], [
                'worker_id.required' => 'Pegawai wajib dipilih.',
                'worker_id.exists' => 'Pegawai yang dipilih tidak valid.',
                'rotation_type.required' => 'Tipe rotasi wajib dipilih.',
                'rotation_type.in' => 'Tipe rotasi harus weekly atau monthly.',
                'shift_sequence.required' => 'Urutan shift wajib diisi minimal satu.',
                'shift_sequence.*.required' => 'Setiap urutan shift wajib diisi.',
                'shift_sequence.*.exists' => 'Salah satu shift pada urutan tidak valid.',
                'start_date.required' => 'Tanggal mulai wajib diisi.',
                'start_date.date' => 'Format tanggal mulai tidak valid.',
                'end_date.date' => 'Format tanggal selesai tidak valid.',
                'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            ]);

            $sequence = array_values(array_filter($data['shift_sequence']));
            if (empty($sequence)) {
                return back()->withInput()->withErrors(['shift_sequence' => 'Urutan shift wajib diisi minimal satu.']);
            }

            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = !empty($data['end_date']) ? Carbon::parse($data['end_date'])->startOfDay() : null;
            $maxPeriods = 12;
            $workerId = $data['worker_id'];

            $deactivateExisting = $request->boolean('deactivate_existing');
            if ($deactivateExisting) {
                $this->deleteOldShifts($workerId);
            } else {
                $this->deleteWorkerShift($id);
            }

            $created = 0;
            $periods = 0;
            $currentStart = $start->copy();
            $sequenceIndex = 0;

            while (true) {
                if ($end && $currentStart->gt($end)) {
                    break;
                }

                if (!$end && $periods >= $maxPeriods) {
                    break;
                }

                if ($data['rotation_type'] === 'weekly') {
                    $periodEnd = $currentStart->copy()->addDays(6);
                } else {
                    $periodEnd = $currentStart->copy()->endOfMonth();
                }

                if ($end && $periodEnd->gt($end)) {
                    $periodEnd = $end->copy();
                }

                $shiftId = $sequence[$sequenceIndex % count($sequence)];

                $this->createWorkerShift([
                    'worker_id' => $workerId,
                    'shift_id' => $shiftId,
                    'start_date' => $currentStart->toDateString(),
                    'end_date' => $periodEnd->toDateString(),
                    'is_active' => true,
                    'notes' => $data['notes'] ?? 'Rotasi otomatis (dari edit)',
                    'skip_deactivate' => true,
                ]);
                $created++;

                $periods++;
                $currentStart = $periodEnd->copy()->addDay();
                $sequenceIndex++;
            }

            return redirect()
                ->route('admin.worker-shifts.index')
                ->with('success', "Rotasi berhasil diperbarui: {$created} jadwal ({$periods} periode) untuk 1 pegawai.");
        }

        // Validasi manual untuk update (bypass WorkerShiftScheduleRequest)
        $data = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ], [
            'worker_id.required' => 'Pegawai wajib dipilih.',
            'worker_id.exists' => 'Pegawai yang dipilih tidak valid.',
            'shift_id.required' => 'Shift wajib dipilih.',
            'shift_id.exists' => 'Shift yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        try {
            // Pastikan is_active terisi
            $data['is_active'] = $request->boolean('is_active'); // Mengambil nilai checkbox dengan benar

            $this->updateWorkerShift($id, $data);

            return redirect()
                ->route('admin.worker-shifts.show', $id)
                ->with('success', 'Jadwal shift berhasil diperbarui');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->deleteWorkerShift($id);

            return redirect()
                ->route('admin.worker-shifts.index')
                ->with('success', 'Jadwal shift berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function workerShifts(string $workerId)
    {
        Worker::findOrFail($workerId);

        return redirect()->route('admin.worker-shifts.index', [
            'worker_id' => $workerId,
        ]);
    }

    /**
     * Get calendar data for shift schedule visualization
     */
    public function calendarData(Request $request)
    {
        try {
            $month = $request->input('month', now()->format('Y-m')); // Format: 2026-02
            $departmentId = $request->input('department_id');
            $shiftIds = $request->input('shift_ids')
                ? explode(',', $request->input('shift_ids'))
                : [];

            // Parse month to get start and end dates
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = Carbon::parse($month . '-01')->endOfMonth();

            // Get calendar grid (including previous/next month dates for complete weeks)
            $calendarStart = $startDate->copy()->startOfWeek();
            $calendarEnd = $endDate->copy()->endOfWeek();

            $days = [];
            $currentDate = $calendarStart->copy();

            while ($currentDate <= $calendarEnd) {
                $dateString = $currentDate->format('Y-m-d');
                $isCurrentMonth = $currentDate->month === $startDate->month;

                // Get workers scheduled for this date (with overrides)
                $workers = \App\Models\Worker::query()
                    ->with([
                        'workerShifts' => function($query) use ($dateString) {
                            $query->where('effective_from', '<=', $dateString)
                                ->where(function($q) use ($dateString) {
                                    $q->whereNull('effective_until')
                                      ->orWhere('effective_until', '>=', $dateString);
                                })
                                ->where('is_active', true)
                                ->with('shift');
                        },
                        'shiftOverrides' => function($query) use ($dateString) {
                            $query->where('override_date', $dateString)->with('shift');
                        },
                        'department'
                    ])
                    ->when($departmentId, function($q) use ($departmentId) {
                        return $q->where('department_id', $departmentId);
                    })
                    ->whereHas('workerShifts', function($query) use ($dateString, $shiftIds) {
                        $query->where('effective_from', '<=', $dateString)
                            ->where(function($q) use ($dateString) {
                                $q->whereNull('effective_until')
                                  ->orWhere('effective_until', '>=', $dateString);
                            })
                            ->where('is_active', true)
                            ->when(count($shiftIds) > 0, function($q) use ($shiftIds) {
                                return $q->whereIn('shift_id', $shiftIds);
                            });
                    })
                    ->get();

                $workersData = $workers->map(function($worker) use ($dateString) {
                    // Check for shift override first (eager load already filters by date)
                    $override = $worker->shiftOverrides->first();
                    $activeShift = null;
                    $shiftSource = 'workershift'; // track source for debugging

                    if ($override && $override->shift) {
                        // Use override shift
                        $activeShift = $override;
                        $shiftSource = 'override';
                    } else {
                        // Fall back to regular worker shift
                        $activeShift = $worker->workerShifts->first();
                        $shiftSource = 'regular';
                    }

                    if (!$activeShift || !$activeShift->shift) {
                        return null;
                    }

                    // Get worker name from various possible fields
                    $workerName = $worker->name ?? $worker->full_name ?? $worker->nama ?? 'Pegawai';
                    $employeeNumber = $worker->employee_number ?? $worker->nip ?? $worker->nik ?? '-';

                    $startTime = $activeShift->shift->start_time ?? null;
                    $endTime = $activeShift->shift->end_time ?? null;
                    $startLabel = $startTime ? Carbon::parse($startTime)->format('H:i') : '00:00';
                    $endLabel = $endTime ? Carbon::parse($endTime)->format('H:i') : '00:00';

                    return [
                        'id' => $worker->id,
                        'name' => $workerName,
                        'employee_number' => $employeeNumber,
                        'department_name' => $worker->department->name ?? 'Tidak ada department',
                        'shift_id' => $activeShift->shift_id,
                        'shift_name' => $activeShift->shift->name ?? 'Shift',
                        'shift_time' => $startLabel . ' - ' . $endLabel,
                        'shift_source' => $shiftSource, // bisa 'override', 'regular'
                    ];
                })->filter()->values()->toArray();

                $days[] = [
                    'date' => $currentDate->day,
                    'fullDate' => $dateString,
                    'isCurrentMonth' => $isCurrentMonth,
                    'isToday' => $currentDate->isToday(),
                    'workers' => $workersData
                ];

                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'days' => $days,
                'month' => $month
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getAllActiveWorkers()
    {
        return Worker::where('status', 'active')->with(['department'])->orderBy('name')->get();
    }

    private function getActiveWorkersByDepartment(string $departmentId)
    {
        return Worker::where('status', 'active')
            ->where('department_id', $departmentId)
            ->with(['department'])
            ->orderBy('name')
            ->get();
    }

    private function getActiveShifts()
    {
        return Shift::where('is_active', true)->orderBy('name')->get();
    }

    private function getActiveDepartments()
    {
        return Department::where('is_active', true)->orderBy('name')->get();
    }

    private function getByWorkerId(string $workerId)
    {
        return WorkerShift::with(['shift'])
            ->where('worker_id', $workerId)
            ->orderByDesc('effective_from')
            ->get();
    }

    private function getShiftHistories(string $workerId)
    {
        return WorkerShiftHistory::where('worker_id', $workerId)
            ->with('shift', 'changedByUser')
            ->orderByDesc('changed_at')
            ->orderByDesc('created_at')
            ->get();
    }

    private function createWorkerShift(array $data)
    {
        return DB::transaction(function () use ($data) {
            $skipDeactivate = (bool) ($data['skip_deactivate'] ?? false);

            if (($data['is_active'] ?? true) && !$skipDeactivate) {
                $this->logShiftsToHistory($data['worker_id'], null, 'shift_replaced');
                $this->deleteOldShifts($data['worker_id']);
            }

            $payload = array_filter($data, function ($value) {
                return $value !== '' && $value !== null && $value !== [];
            });

            $payload['effective_from'] = $payload['effective_from'] ?? ($payload['start_date'] ?? null);
            $payload['effective_until'] = array_key_exists('effective_until', $payload)
                ? $payload['effective_until']
                : ($payload['end_date'] ?? null);
            $payload['notes'] = $payload['notes'] ?? ($payload['description'] ?? null);

            unset($payload['skip_deactivate'], $payload['start_date'], $payload['end_date'], $payload['description'], $payload['worker_ids']);

            return WorkerShift::create($payload);
        });
    }

    private function updateWorkerShift(string $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $workerShift = WorkerShift::findOrFail($id);

            $this->logShiftsToHistory($workerShift->worker_id, $id, 'shift_replaced');
            $this->deleteOldShifts($workerShift->worker_id, $id);

            $payload = array_filter($data, function ($value) {
                return $value !== '' && $value !== null && $value !== [];
            });

            $payload['effective_from'] = $payload['effective_from'] ?? ($payload['start_date'] ?? null);
            $payload['effective_until'] = array_key_exists('effective_until', $payload)
                ? $payload['effective_until']
                : ($payload['end_date'] ?? null);
            $payload['notes'] = $payload['notes'] ?? ($payload['description'] ?? null);

            unset($payload['start_date'], $payload['end_date'], $payload['description']);

            $workerShift->update($payload);
            return $workerShift->fresh(['worker.department', 'shift']);
        });
    }

    private function deleteWorkerShift(string $id): bool
    {
        $workerShift = WorkerShift::findOrFail($id);

        WorkerShiftHistory::logChange(
            $workerShift->worker_id,
            $workerShift->shift_id,
            $workerShift->effective_from?->toDateString(),
            $workerShift->effective_until?->toDateString(),
            'shift_deleted'
        );

        return (bool) $workerShift->delete();
    }

    private function deleteOldShifts(string $workerId, ?string $excludeId = null): int
    {
        $query = WorkerShift::where('worker_id', $workerId);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->delete();
    }

    private function logShiftsToHistory(string $workerId, ?string $excludeId = null, string $reason = 'shift_replaced'): void
    {
        $query = WorkerShift::where('worker_id', $workerId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingShifts = $query->get();

        foreach ($existingShifts as $shift) {
            WorkerShiftHistory::logChange(
                $shift->worker_id,
                $shift->shift_id,
                $shift->effective_from?->toDateString(),
                $shift->effective_until?->toDateString(),
                $reason
            );
        }
    }
}
