<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShiftSwap\ShiftSwapService;
use Illuminate\Http\JsonResponse;

class WorkerShiftApiController extends Controller
{
    public function __construct(protected ShiftSwapService $shiftSwapService)
    {
        $this->middleware('auth');
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