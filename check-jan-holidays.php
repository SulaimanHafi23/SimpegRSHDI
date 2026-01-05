<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "All January 2026 holidays in database:\n";
echo str_repeat("=", 60) . "\n";

$all = App\Models\Holiday::whereYear('date', 2026)->whereMonth('date', 1)->orderBy('date')->get();
foreach($all as $h) {
    echo "ID: {$h->id} | Date: {$h->date->format('Y-m-d')} | Name: {$h->name}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Searching for wrong dates...\n\n";

$wrong2 = App\Models\Holiday::where('date', '2026-01-02')->get();
if($wrong2->count() > 0) {
    echo "❌ Found on Jan 2: {$wrong2->count()} record(s)\n";
    foreach($wrong2 as $w) {
        echo "   - ID {$w->id}: {$w->name}\n";
    }
} else {
    echo "✅ No holidays on Jan 2 (correct!)\n";
}

$wrong17 = App\Models\Holiday::where('date', '2026-01-17')->get();
if($wrong17->count() > 0) {
    echo "❌ Found on Jan 17: {$wrong17->count()} record(s)\n";
    foreach($wrong17 as $w) {
        echo "   - ID {$w->id}: {$w->name}\n";
    }
} else {
    echo "✅ No holidays on Jan 17 (correct!)\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Testing API endpoint...\n";

// Simulate API call
$start = '2026-01-01';
$end = '2026-01-31';
$holidays = App\Models\Holiday::dateRange($start, $end)->get();

echo "\nAPI would return {$holidays->count()} holidays for January 2026:\n";
foreach($holidays as $holiday) {
    $startDate = $holiday->date->format('Y-m-d');
    $endDate = \Carbon\Carbon::parse($holiday->date)->addDay()->format('Y-m-d');
    echo "  - {$holiday->name}\n";
    echo "    Start: {$startDate}\n";
    echo "    End: {$endDate}\n";
}
