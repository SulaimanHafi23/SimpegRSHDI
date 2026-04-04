<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Services\ShiftSwap\ShiftSwapService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerShiftApiController extends Controller
{
    public function __construct(protected ShiftSwapService $shiftSwapService)
    {
        $this->middleware('auth');
    }

    /**
     * Get the shift end time (and start time) for a worker on a given date.
      * Used by employee scheduling forms to auto-fill shift time information.
     *
     * GET /api/workers/{workerId}/shift-time?date=YYYY-MM-DD
     */
    public function getShiftTime(string $workerId, Request $request): JsonResponse
    {
        try {
            $worker = Worker::with([
                'shift',
                'workerShifts.shift',
                'shiftOverrides.shift',
            ])->findOrFail($workerId);

            $date = $request->query('date', now()->format('Y-m-d'));
            $carbonDate = Carbon::parse($date);

            // 1. Check ShiftOverride for this date
            $override = $worker->shiftOverrides
                ->where('override_date', $carbonDate->format('Y-m-d'))
                ->first();

            if ($override && $override->shift) {
                $shift = $override->shift;
            } else {
                // 2. Check active WorkerShift for this date
                $workerShift = $worker->workerShifts
                    ->where('is_active', true)
                    ->filter(function ($ws) use ($carbonDate) {
                        $effectiveFrom = Carbon::parse($ws->effective_from);
                        $effectiveUntil = $ws->effective_until ? Carbon::parse($ws->effective_until) : null;
                        return $effectiveFrom->lte($carbonDate) && (!$effectiveUntil || $effectiveUntil->gte($carbonDate));
                    })
                    ->sortByDesc('effective_from')
                    ->first();

                $shift = $workerShift?->shift ?? $worker->shift;
            }

            if (!$shift) {
                return response()->json(['error' => 'Shift tidak ditemukan untuk pegawai ini'], 404);
            }

            // Get schedule for the specific day of week (handles ShiftDayTime overrides)
            $schedule = $shift->getScheduleForDate($carbonDate);

            return response()->json([
                'shift_name'  => $shift->name,
                'start_time'  => substr($schedule['start_time'], 0, 5), // "HH:mm"
                'end_time'    => substr($schedule['end_time'], 0, 5),   // "HH:mm"
                'is_overnight' => $schedule['is_overnight'],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Pegawai tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data shift'], 500);
        }
    }

    /**
     * Get future shifts for a specific worker
     */
    public function getFutureShifts(string $workerId): JsonResponse
    {
        try {
            $shifts = $this->shiftSwapService->getFutureShifts($workerId);

            $formattedShifts = $shifts->map(function ($workerShift) {
                return [
                    'id' => $workerShift->id,
                    'date' => $workerShift->effective_from->format('d M Y'),
                    'shift_name' => $workerShift->shift?->name ?? 'N/A',
                    'start_time' => $workerShift->shift ? \Carbon\Carbon::parse($workerShift->shift->start_time)->format('H:i') : '',
                    'end_time' => $workerShift->shift ? \Carbon\Carbon::parse($workerShift->shift->end_time)->format('H:i') : '',
                ];
            });

            return response()->json($formattedShifts);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load shifts'], 500);
        }
    }
}
