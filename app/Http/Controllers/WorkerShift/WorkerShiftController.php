<?php

namespace App\Http\Controllers\WorkerShift;

use App\Http\Controllers\Controller;
use App\Services\WorkerShift\WorkerShiftService;
use App\Services\Worker\WorkerService;
use App\Services\Master\ShiftService;
use App\Services\Master\DepartmentService;
use App\Http\Requests\Schedule\WorkerShiftScheduleRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkerShiftController extends Controller
{
    public function __construct(
        protected WorkerShiftService $workerShiftService,
        protected WorkerService $workerService,
        protected ShiftService $shiftService,
        protected DepartmentService $departmentService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'worker_id' => $request->worker_id,
            'shift_id' => $request->shift_id,
            'is_active' => $request->is_active,
            'per_page' => $request->per_page ?? 15,
        ];

        // Get all active workers with their latest shift
        $workers = $this->workerService->getAllActive();
        
        // Get all workers with their latest active shift
        $workersWithShifts = $workers->map(function($worker) use ($filters) {
            // Get latest active shift for this worker
            $latestShift = $worker->workerShifts()
                ->with(['shift'])
                ->when(isset($filters['shift_id']), function($q) use ($filters) {
                    return $q->where('shift_id', $filters['shift_id']);
                })
                ->when(isset($filters['is_active']), function($q) use ($filters) {
                    return $q->where('is_active', $filters['is_active']);
                })
                ->orderBy('effective_from', 'desc')
                ->first();
            
            $worker->latestShift = $latestShift;
            return $worker;
        });

        // Apply worker filter if specified
        if (isset($filters['worker_id']) && $filters['worker_id']) {
            $workersWithShifts = $workersWithShifts->where('id', $filters['worker_id']);
        }

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

        $shifts = $this->shiftService->getActive();
        $departments = $this->departmentService->getAllActive();

        return view('admin.schedules.index', compact('workersWithShifts', 'workers', 'shifts', 'departments', 'filters'));
    }

    public function create()
    {
        $workers = $this->workerService->getAllActive();
        $shifts = $this->shiftService->getActive();

        return view('admin.schedules.create', compact('workers', 'shifts'));
    }

    public function generate()
    {
        $workers = $this->workerService->getAllActive();
        $shifts = $this->shiftService->getActive();

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
                $this->workerShiftService->deactivateOldShifts($workerId);
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
                $this->workerShiftService->create([
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
        // Validasi manual untuk menghindari konflik 'required_without' 
        // yang menyebabkan error "Pegawai field is required when Pegawai is not present"
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

                $this->workerShiftService->create($payload);
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
        $workerShift = $this->workerShiftService->getById($id);

        return view('admin.schedules.show', compact('workerShift'));
    }

    public function edit(string $id)
    {
        $workerShift = $this->workerShiftService->getById($id);
        $workers = $this->workerService->getAllActive();
        $shifts = $this->shiftService->getActive();

        return view('admin.schedules.edit', compact('workerShift', 'workers', 'shifts'));
    }

    public function update(Request $request, string $id)
    {
        // Validasi manual untuk update (bypass WorkerShiftScheduleRequest)
        $data = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ], [
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

            $this->workerShiftService->update($id, $data);

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
            $this->workerShiftService->delete($id);

            return redirect()
                ->route('admin.worker-shifts.index')
                ->with('success', 'Jadwal shift berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function workerShifts(string $workerId)
    {
        $worker = $this->workerService->getById($workerId);
        $workerShifts = $this->workerShiftService->getByWorkerId($workerId);

        return view('admin.schedules.worker-shifts', compact('worker', 'workerShifts'));
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

                // Get workers scheduled for this date
                $workers = \App\Models\Worker::query()
                    ->with(['workerShifts' => function($query) use ($dateString) {
                        $query->where('effective_from', '<=', $dateString)
                            ->where(function($q) use ($dateString) {
                                $q->whereNull('effective_until')
                                  ->orWhere('effective_until', '>=', $dateString);
                            })
                            ->where('is_active', true)
                            ->with('shift');
                    }, 'department'])
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
                    $activeShift = $worker->workerShifts->first();
                    
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
}
