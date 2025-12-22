<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display holidays list
     */
    public function index(Request $request)
    {
        $query = Holiday::query();

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        } else {
            // Default: current year and next year
            $currentYear = now()->year;
            $query->whereIn(\DB::raw('YEAR(date)'), [$currentYear, $currentYear + 1]);
        }

        $holidays = $query->orderBy('date', 'asc')->paginate(20);
        
        $years = Holiday::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('admin.holidays.index', compact('holidays', 'years'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.holidays.create');
    }

    /**
     * Store new holiday
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_national' => 'boolean',
        ]);

        Holiday::create($validated);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Libur nasional berhasil ditambahkan.');
    }

    /**
     * Show edit form
     */
    public function edit(string $id)
    {
        $holiday = Holiday::findOrFail($id);
        return view('admin.holidays.edit', compact('holiday'));
    }

    /**
     * Update holiday
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_national' => 'boolean',
        ]);

        $holiday = Holiday::findOrFail($id);
        $holiday->update($validated);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Libur nasional berhasil diperbarui.');
    }

    /**
     * Delete holiday
     */
    public function destroy(string $id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Libur nasional berhasil dihapus.');
    }

    /**
     * Bulk create form for a year
     */
    public function bulkCreate()
    {
        return view('admin.holidays.bulk-create');
    }

    /**
     * Store bulk holidays
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2024|max:2050',
            'holidays' => 'required|array|min:1',
            'holidays.*.name' => 'required|string|max:255',
            'holidays.*.date' => 'required|date',
            'holidays.*.description' => 'nullable|string',
        ]);

        $count = 0;
        foreach ($validated['holidays'] as $holidayData) {
            Holiday::create([
                'name' => $holidayData['name'],
                'date' => $holidayData['date'],
                'description' => $holidayData['description'] ?? null,
                'is_national' => true,
            ]);
            $count++;
        }

        return redirect()->route('admin.holidays.index')
            ->with('success', "Berhasil menambahkan {$count} libur nasional.");
    }
}
