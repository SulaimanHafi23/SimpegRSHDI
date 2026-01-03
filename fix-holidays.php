<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Cleaning up holidays...\n";

// Hapus Tahun Baru Masehi yang salah (tanggal 2)
$deleted = App\Models\Holiday::where('date', '2026-01-02')
    ->where('name', 'LIKE', '%Tahun Baru%')
    ->delete();
if ($deleted > 0) {
    echo "✓ Deleted wrong New Year date (Jan 2): $deleted record(s)\n";
}

// Hapus Isra Miraj duplikat di Februari 2026
$deleted = App\Models\Holiday::where('date', '2026-02-16')
    ->where('name', 'LIKE', '%Isra%')
    ->delete();
if ($deleted > 0) {
    echo "✓ Deleted duplicate Isra Miraj (Feb 16): $deleted record(s)\n";
}

// Cek apakah Tahun Baru Masehi 2026 yang benar sudah ada
$exists = App\Models\Holiday::where('date', '2026-01-01')
    ->where('name', 'LIKE', '%Tahun Baru Masehi%')
    ->exists();
if (!$exists) {
    App\Models\Holiday::create([
        'name' => 'Tahun Baru Masehi',
        'date' => '2026-01-01',
        'description' => 'Hari Raya Tahun Baru 2026',
        'is_national' => true,
    ]);
    echo "✓ Added correct New Year date (Jan 1)\n";
} else {
    echo "✓ Correct New Year date already exists\n";
}

// Cek apakah Isra Miraj 2026 yang benar sudah ada
$exists = App\Models\Holiday::where('date', '2026-01-27')
    ->where('name', 'LIKE', '%Isra%')
    ->exists();
if (!$exists) {
    App\Models\Holiday::create([
        'name' => 'Isra Mi\'raj Nabi Muhammad SAW',
        'date' => '2026-01-27',
        'description' => 'Peringatan Isra Mi\'raj',
        'is_national' => true,
    ]);
    echo "✓ Added correct Isra Miraj date (Jan 27)\n";
} else {
    echo "✓ Correct Isra Miraj date already exists\n";
}

echo "\n✅ Cleanup completed!\n";

// Show January 2026 holidays
echo "\nJanuary 2026 holidays:\n";
$janHolidays = App\Models\Holiday::whereYear('date', 2026)
    ->whereMonth('date', 1)
    ->orderBy('date')
    ->get();
foreach ($janHolidays as $holiday) {
    echo "  - {$holiday->date->format('Y-m-d')}: {$holiday->name}\n";
}
