<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkerOffDayController extends Controller
{
    public function __construct()
    {
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
        $validated['created_by'] = Auth::id();

        $pattern = $worker->offDays()->create([
            'day_of_week' => $validated['day_of_week'],
            'effective_from' => $validated['effective_from'],
            'effective_until' => $validated['effective_until'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'created_by' => $validated['created_by'],
        ]);

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
        $isOffDay = $worker->isOffDay(Carbon::parse($validated['date']));
        $info = $this->getOffDayInfo($worker, $validated['date']);

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
        $offDays = [];
        $start = Carbon::parse($validated['from']);
        $end = Carbon::parse($validated['to']);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($worker->isOffDay($date)) {
                $offDays[] = $date->format('Y-m-d');
            }
        }

        return response()->json([
            'success' => true,
            'off_days' => $offDays,
        ]);
    }

    private function getOffDayInfo(Worker $worker, string $date): ?array
    {
        $dateObj = Carbon::parse($date);
        $dateStr = $dateObj->format('Y-m-d');

        $offDayPattern = $worker->offDays()
            ->where('effective_from', '<=', $dateStr)
            ->where(function ($query) use ($dateStr) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $dateStr);
            })
            ->orderByDesc('effective_from')
            ->get()
            ->first(function ($item) use ($dateObj) {
                return is_array($item->day_of_week) && in_array($dateObj->dayOfWeek, $item->day_of_week, true);
            });

        if (!$offDayPattern) {
            return null;
        }

        $isSingleDay = $offDayPattern->effective_until
            && $offDayPattern->effective_from
            && $offDayPattern->effective_from->format('Y-m-d') === $offDayPattern->effective_until->format('Y-m-d');

        return [
            'type' => $isSingleDay ? 'single' : 'recurring',
            'date' => $dateObj->format('Y-m-d'),
            'pattern' => $offDayPattern->day_of_week,
            'reason' => $offDayPattern->reason,
            'effective_from' => $offDayPattern->effective_from->format('d M Y'),
            'effective_until' => $offDayPattern->effective_until?->format('d M Y') ?? 'Tanpa batas',
        ];
    }
}
