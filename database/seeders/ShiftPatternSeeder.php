<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShiftPattern;

class ShiftPatternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patterns = [
            ['name' => 'Every Day', 'days_of_week' => ['mon','tue','wed','thu','fri','sat','sun']],
            ['name' => 'Weekdays (Mon-Fri)', 'days_of_week' => ['mon','tue','wed','thu','fri']],
            ['name' => 'Weekends (Sat-Sun)', 'days_of_week' => ['sat','sun']],
            ['name' => 'Mon-Wed-Fri', 'days_of_week' => ['mon','wed','fri']],
        ];

        foreach ($patterns as $p) {
            ShiftPattern::create($p);
        }
    }
}
