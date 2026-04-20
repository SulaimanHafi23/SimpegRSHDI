<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    public function autoGenerate(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $selectedYear = $validated['year'] ?? now()->year;
        $previewHolidays = null;
        $fetchError = null;

        if ($request->filled('year')) {
            try {
                $holidays = $this->getIndonesianNationalHolidays($selectedYear);

                $existingDates = Holiday::whereIn('date', collect($holidays)->pluck('date')->unique()->values())
                    ->pluck('date')
                    ->map(fn ($date) => \Carbon\Carbon::parse($date)->toDateString())
                    ->flip();

                $previewHolidays = collect($holidays)
                    ->map(function (array $holiday) use ($existingDates) {
                        $holiday['already_exists'] = $existingDates->has($holiday['date']);
                        return $holiday;
                    })
                    ->values();
            } catch (\Throwable $e) {
                Log::error('Failed to fetch holiday preview from API.', [
                    'year' => $selectedYear,
                    'endpoint' => 'https://libur.deno.dev/api',
                    'message' => $e->getMessage(),
                ]);
                $fetchError = 'Gagal mengambil data libur dari API. Silakan coba lagi.';
            }
        }

        $availableYears = $this->getAvailableYearsForGeneration();

        return view('admin.holidays.auto-generate', compact(
            'availableYears',
            'selectedYear',
            'previewHolidays',
            'fetchError'
        ));
    }

    /**
     * Store auto-generated holidays
     */
    public function storeAutoGenerate(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = $validated['year'];
        try {
            $holidays = $this->getIndonesianNationalHolidays($year);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch holidays before storing auto-generated data.', [
                'year' => $year,
                'endpoint' => 'https://libur.deno.dev/api',
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('admin.holidays.auto-generate', ['year' => $year])
                ->with('error', 'Gagal sinkronisasi data libur dari API. Silakan coba lagi beberapa saat.');
        }

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
    private function getIndonesianNationalHolidays(int $year): array
    {
        $response = Http::timeout(20)
            ->retry(3, 500)
            ->acceptJson()
            ->get('https://libur.deno.dev/api', ['year' => $year]);

        if (! $response->successful()) {
            Log::warning('Holiday API responded with non-success status.', [
                'year' => $year,
                'endpoint' => 'https://libur.deno.dev/api',
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to fetch holidays from API.');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            Log::warning('Holiday API payload is not an array.', [
                'year' => $year,
                'endpoint' => 'https://libur.deno.dev/api',
                'payload_type' => gettype($payload),
            ]);
            throw new \RuntimeException('Unexpected API response format.');
        }

        return collect($payload)
            ->filter(fn ($item) => is_array($item) && ! empty($item['date']) && ! empty($item['name']))
            ->map(function (array $item) use ($year) {
                $date = \Carbon\Carbon::parse($item['date'])->toDateString();
                $name = trim((string) preg_replace('/\s+/', ' ', (string) $item['name']));

                return [
                    'name' => $name,
                    'date' => $date,
                    'description' => "Hari Libur Pada Tahun {$year}",
                ];
            })
            ->unique('date')
            ->sortBy('date')
            ->values()
            ->all();
    }

    /**
     * Get available years for auto-generation
     */
    private function getAvailableYearsForGeneration()
    {
        $currentYear = now()->year;

        return collect(range($currentYear - 2, $currentYear + 5))
            ->map(fn ($year) => ['year' => $year])
            ->values()
            ->all();
    }
}
