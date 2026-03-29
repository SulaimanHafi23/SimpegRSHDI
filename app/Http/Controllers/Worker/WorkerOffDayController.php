<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Services\WorkerOffDay\WorkerOffDayService;
use App\DTOs\WorkerOffDay\WorkerOffDayDTO;
use Illuminate\Http\Request;

class WorkerOffDayController extends Controller
{
    public function __construct(
        protected WorkerOffDayService $offDayService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:worker.manage');
    }

    /**
     * Get all off-day rules for worker
     */
    public function index(Request $request, $workerId)
    {
        $worker = Worker::findOrFail($workerId);
        $patterns = $worker->offDays()->orderBy('effective_from', 'desc')->get();

        return response()->json([
            'success' => true,
            'patterns' => $patterns,
        ]);
    }

    /**
     * Store off-day rule (single day or recurring)
     */
    public function storePattern(Request $request, $workerId)
    {
        $type = $request->input('type', 'recurring');

        if ($type === 'single') {
            $validated = $request->validate([
                'type' => 'required|in:single,recurring',
                'single_date' => 'required|date_format:Y-m-d',
                'reason' => 'nullable|string|max:255',
            ]);

            $singleDate = \Carbon\Carbon::parse($validated['single_date']);
            $validated['day_of_week'] = [$singleDate->dayOfWeek];
            $validated['effective_from'] = $validated['single_date'];
            $validated['effective_until'] = $validated['single_date'];
        } else {
            $validated = $request->validate([
                'type' => 'required|in:single,recurring',
                'day_of_week' => 'required|array|min:1',
                'day_of_week.*' => 'integer|min:0|max:6',
                'effective_from' => 'required|date_format:Y-m-d',
                'effective_until' => 'nullable|date_format:Y-m-d|after_or_equal:effective_from',
                'reason' => 'nullable|string|max:255',
            ]);
        }

        $worker = Worker::findOrFail($workerId);
        $validated['worker_id'] = $workerId;
        $validated['created_by'] = auth()->id();

        $dto = WorkerOffDayDTO::fromRequest($validated);
        $pattern = $worker->offDays()->create($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil ditambahkan',
            'data' => $pattern,
        ], 201);
    }

    /**
     * Delete off-day pattern
     */
    public function destroyPattern(Request $request, $workerId, $patternId)
    {
        $worker = Worker::findOrFail($workerId);
        $offDay = $worker->offDays()->findOrFail($patternId);
        $offDay->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil dihapus',
        ]);
    }

    /**
     * Check if date is off-day
     */
    public function checkDate(Request $request, $workerId)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $worker = Worker::findOrFail($workerId);
        $isOffDay = $this->offDayService->isOffDay($worker, $validated['date']);
        $info = $this->offDayService->getOffDayInfo($worker, $validated['date']);

        return response()->json([
            'success' => true,
            'is_off_day' => $isOffDay,
            'info' => $info,
        ]);
    }

    /**
     * Get off-days in date range
     */
    public function getRange(Request $request, $workerId)
    {
        $validated = $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        $worker = Worker::findOrFail($workerId);
        $offDays = $this->offDayService->getOffDaysInRange($worker, $validated['from'], $validated['to']);

        return response()->json([
            'success' => true,
            'off_days' => $offDays,
        ]);
    }
}
