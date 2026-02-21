<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerOffDayException;
use App\Models\WorkerOffDay;
use App\Services\WorkerOffDay\WorkerOffDayService;
use App\DTOs\WorkerOffDay\WorkerOffDayExceptionDTO;
use App\DTOs\WorkerOffDay\WorkerOffDayDTO;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkerOffDayController extends Controller
{
    public function __construct(
        protected WorkerOffDayService $offDayService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:worker.manage');
    }

    /**
     * Get all off-days (exceptions + patterns) for worker
     */
    public function index(Request $request, $workerId)
    {
        $worker = Worker::findOrFail($workerId);

        $exceptions = $worker->offDayExceptions()->orderBy('off_date', 'desc')->get();
        $patterns = $worker->offDays()->orderBy('effective_from', 'desc')->get();

        return response()->json([
            'success' => true,
            'exceptions' => $exceptions,
            'patterns' => $patterns,
        ]);
    }

    /**
     * Store new off-day exception
     */
    public function storeException(Request $request, $workerId)
    {
        $validated = $request->validate([
            'type' => 'required|in:single,recurring',
            'off_date' => 'required|date_format:Y-m-d',
            'day_of_week' => 'nullable|array', // for recurring
            'recurring_pattern' => 'nullable|array',
            'reason' => 'nullable|string|max:255',
        ]);

        $worker = Worker::findOrFail($workerId);
        $validated['worker_id'] = $workerId;
        $validated['created_by'] = auth()->id();

        $dto = WorkerOffDayExceptionDTO::fromRequest($validated);
        $exception = $worker->offDayExceptions()->create($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Off-day exception berhasil ditambahkan',
            'data' => $exception,
        ], 201);
    }

    /**
     * Store new off-day pattern (rotating)
     */
    public function storePattern(Request $request, $workerId)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|array|min:1',
            'day_of_week.*' => 'integer|min:0|max:6',
            'effective_from' => 'required|date_format:Y-m-d',
            'effective_until' => 'nullable|date_format:Y-m-d|after_or_equal:effective_from',
            'reason' => 'nullable|string|max:255',
        ]);

        $worker = Worker::findOrFail($workerId);
        $validated['worker_id'] = $workerId;
        $validated['created_by'] = auth()->id();

        $dto = WorkerOffDayDTO::fromRequest($validated);
        $pattern = $worker->offDays()->create($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Pola hari libur berhasil ditambahkan',
            'data' => $pattern,
        ], 201);
    }

    /**
     * Delete off-day exception
     */
    public function destroyException(Request $request, $workerId, $exceptionId)
    {
        $worker = Worker::findOrFail($workerId);
        $exception = $worker->offDayExceptions()->findOrFail($exceptionId);
        $exception->delete();

        return response()->json([
            'success' => true,
            'message' => 'Off-day exception berhasil dihapus',
        ]);
    }

    /**
     * Delete off-day pattern
     */
    public function destroyPattern(Request $request, $workerId, $patternId)
    {
        $worker = Worker::findOrFail($workerId);
        $pattern = $worker->offDays()->findOrFail($patternId);
        $pattern->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pola hari libur berhasil dihapus',
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
