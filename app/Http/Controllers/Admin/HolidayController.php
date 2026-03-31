<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:holiday.manage');
    }

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
            $query->whereIn(DB::raw('YEAR(date)'), [$currentYear, $currentYear + 1]);
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

        $submittedDates = collect($validated['holidays'])
            ->map(fn ($holiday) => \Carbon\Carbon::parse($holiday['date'])->toDateString())
            ->unique()
            ->values();

        $existingDates = Holiday::whereIn('date', $submittedDates)
            ->pluck('date')
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->toDateString())
            ->flip();

        $created = 0;
        $skipped = 0;
        $seenDates = [];

        foreach ($validated['holidays'] as $holidayData) {
            $date = \Carbon\Carbon::parse($holidayData['date'])->toDateString();

            if (isset($seenDates[$date]) || $existingDates->has($date)) {
                $skipped++;
                continue;
            }

            Holiday::create([
                'name' => $holidayData['name'],
                'date' => $date,
                'description' => $holidayData['description'] ?? null,
                'is_national' => true,
            ]);

            $seenDates[$date] = true;
            $created++;
        }

        $message = "Berhasil menambahkan {$created} libur nasional.";
        if ($skipped > 0) {
            $message .= " ({$skipped} data dilewati karena sudah ada)";
        }

        return redirect()->route('admin.holidays.index')
            ->with('success', $message);
    }

    /**
     * Show auto-generate form
     */
    public function autoGenerate()
    {
        $availableYears = $this->getAvailableYearsForGeneration();
        return view('admin.holidays.auto-generate', compact('availableYears'));
    }

    /**
     * Store auto-generated holidays
     */
    public function storeAutoGenerate(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|in:2025,2026',
        ]);

        $year = $validated['year'];
        $holidays = $this->getIndonesianNationalHolidays($year);

        $count = 0;
        $skipped = 0;

        foreach ($holidays as $holidayData) {
            // Check if holiday already exists
            $exists = Holiday::where('date', $holidayData['date'])->exists();

            if (!$exists) {
                Holiday::create([
                    'name' => $holidayData['name'],
                    'date' => $holidayData['date'],
                    'description' => $holidayData['description'],
                    'is_national' => true,
                ]);
                $count++;
            } else {
                $skipped++;
            }
        }

        $message = "Berhasil menambahkan {$count} libur nasional untuk tahun {$year}.";
        if ($skipped > 0) {
            $message .= " ({$skipped} libur dilewati karena sudah ada)";
        }

        return redirect()->route('admin.holidays.index')
            ->with('success', $message);
    }

    /**
     * Get Indonesian national holidays for a specific year
     */
    private function getIndonesianNationalHolidays($year)
    {
        $holidays = [];

        if ($year == 2025) {
            $holidays = [
                ['name' => 'Tahun Baru Masehi', 'date' => '2025-01-01', 'description' => 'Hari Raya Tahun Baru 2025'],
                ['name' => 'Tahun Baru Imlek 2576 Kongzili', 'date' => '2025-01-29', 'description' => 'Tahun Baru China/Imlek'],
                ['name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'date' => '2025-01-27', 'description' => 'Peringatan Isra Mi\'raj'],
                ['name' => 'Hari Raya Nyepi Tahun Baru Saka 1947', 'date' => '2025-03-29', 'description' => 'Tahun Baru Saka'],
                ['name' => 'Wafat Isa Al-Masih', 'date' => '2025-04-18', 'description' => 'Jumat Agung'],
                ['name' => 'Hari Buruh Internasional', 'date' => '2025-05-01', 'description' => 'Hari Buruh Sedunia'],
                ['name' => 'Kenaikan Isa Al-Masih', 'date' => '2025-05-29', 'description' => 'Kenaikan Yesus Kristus'],
                ['name' => 'Hari Raya Idulfitri 1446 H', 'date' => '2025-03-31', 'description' => 'Hari Raya Idul Fitri Hari Pertama'],
                ['name' => 'Hari Raya Idulfitri 1446 H', 'date' => '2025-04-01', 'description' => 'Hari Raya Idul Fitri Hari Kedua'],
                ['name' => 'Cuti Bersama Idulfitri', 'date' => '2025-03-28', 'description' => 'Cuti Bersama'],
                ['name' => 'Cuti Bersama Idulfitri', 'date' => '2025-04-02', 'description' => 'Cuti Bersama'],
                ['name' => 'Cuti Bersama Idulfitri', 'date' => '2025-04-03', 'description' => 'Cuti Bersama'],
                ['name' => 'Cuti Bersama Idulfitri', 'date' => '2025-04-04', 'description' => 'Cuti Bersama'],
                ['name' => 'Hari Lahir Pancasila', 'date' => '2025-06-01', 'description' => 'Hari Kesaktian Pancasila'],
                ['name' => 'Hari Raya Iduladha 1446 H', 'date' => '2025-06-07', 'description' => 'Hari Raya Idul Adha'],
                ['name' => 'Tahun Baru Islam 1447 H', 'date' => '2025-06-27', 'description' => 'Tahun Baru Hijriyah'],
                ['name' => 'Hari Kemerdekaan RI', 'date' => '2025-08-17', 'description' => 'HUT Kemerdekaan Indonesia ke-80'],
                ['name' => 'Maulid Nabi Muhammad SAW', 'date' => '2025-09-05', 'description' => 'Peringatan Maulid Nabi'],
                ['name' => 'Hari Raya Natal', 'date' => '2025-12-25', 'description' => 'Hari Raya Natal'],
                ['name' => 'Cuti Bersama Natal', 'date' => '2025-12-26', 'description' => 'Cuti Bersama'],
            ];
        } elseif ($year == 2026) {
            $holidays = [
                ['name' => 'Tahun Baru Masehi', 'date' => '2026-01-01', 'description' => 'Hari Raya Tahun Baru 2026'],
                ['name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'date' => '2026-01-16', 'description' => 'Peringatan Isra Mi\'raj'],
                ['name' => 'Tahun Baru Imlek 2577 Kongzili', 'date' => '2026-02-17', 'description' => 'Tahun Baru China/Imlek'],
                ['name' => 'Hari Raya Nyepi Tahun Baru Saka 1948', 'date' => '2026-03-19', 'description' => 'Tahun Baru Saka'],
                ['name' => 'Hari Raya Idulfitri 1447 H', 'date' => '2026-03-20', 'description' => 'Hari Raya Idul Fitri Hari Pertama'],
                ['name' => 'Hari Raya Idulfitri 1447 H', 'date' => '2026-03-21', 'description' => 'Hari Raya Idul Fitri Hari Kedua'],
                ['name' => 'Cuti Bersama Idulfitri', 'date' => '2026-03-23', 'description' => 'Cuti Bersama'],
                ['name' => 'Cuti Bersama Idulfitri', 'date' => '2026-03-24', 'description' => 'Cuti Bersama'],
                ['name' => 'Wafat Isa Al-Masih', 'date' => '2026-04-03', 'description' => 'Jumat Agung'],
                ['name' => 'Hari Buruh Internasional', 'date' => '2026-05-01', 'description' => 'Hari Buruh Sedunia'],
                ['name' => 'Kenaikan Isa Al-Masih', 'date' => '2026-05-14', 'description' => 'Kenaikan Yesus Kristus'],
                ['name' => 'Hari Raya Iduladha 1447 H', 'date' => '2026-05-27', 'description' => 'Hari Raya Idul Adha'],
                ['name' => 'Hari Lahir Pancasila', 'date' => '2026-06-01', 'description' => 'Hari Kesaktian Pancasila'],
                ['name' => 'Tahun Baru Islam 1448 H', 'date' => '2026-06-16', 'description' => 'Tahun Baru Hijriyah'],
                ['name' => 'Hari Kemerdekaan RI', 'date' => '2026-08-17', 'description' => 'HUT Kemerdekaan Indonesia ke-81'],
                ['name' => 'Maulid Nabi Muhammad SAW', 'date' => '2026-08-25', 'description' => 'Peringatan Maulid Nabi'],
                ['name' => 'Hari Raya Natal', 'date' => '2026-12-25', 'description' => 'Hari Raya Natal'],
            ];
        }

        return $holidays;
    }

    /**
     * Get available years for auto-generation
     */
    private function getAvailableYearsForGeneration()
    {
        return [
            ['year' => 2025, 'count' => count($this->getIndonesianNationalHolidays(2025))],
            ['year' => 2026, 'count' => count($this->getIndonesianNationalHolidays(2026))],
        ];
    }
}
