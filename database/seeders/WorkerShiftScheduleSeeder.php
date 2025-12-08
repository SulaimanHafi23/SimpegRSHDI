<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkerShiftSchedule;
use App\Models\Worker;
use App\Models\Shift;
use Carbon\Carbon;

class WorkerShiftScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📅 Starting WorkerShiftScheduleSeeder...');

        // Get workers & shifts
        $workers = Worker::with('position')->get();
        $shifts = Shift::all();

        if ($workers->isEmpty() || $shifts->isEmpty()) {
            $this->command->warn('⚠️  Workers or Shifts not found. Skipping...');
            return;
        }

        $shiftPagi = $shifts->firstWhere('name', 'Pagi');
        $shiftSiang = $shifts->firstWhere('name', 'Siang');
        $shiftMalam = $shifts->firstWhere('name', 'Malam');

        if (!$shiftPagi || !$shiftSiang || !$shiftMalam) {
            $this->command->error('❌ Shift Pagi/Siang/Malam tidak ditemukan!');
            return;
        }

        $this->command->info('🔄 Creating default recurring schedules...');

        // Filter workers by position
        $perawatList = $workers->filter(fn($w) => stripos($w->position->name ?? '', 'perawat') !== false);
        $dokterList = $workers->filter(fn($w) => stripos($w->position->name ?? '', 'dokter') !== false);
        $bidanList = $workers->filter(fn($w) => stripos($w->position->name ?? '', 'bidan') !== false);

        // Perawat: Shift Pagi, Senin-Jumat
        foreach ($perawatList->take(2) as $perawat) {
            WorkerShiftSchedule::setDefaultSchedule(
                $perawat->id,
                $shiftPagi->id,
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'Jadwal rutin shift pagi'
            );
            $this->command->info("   ✅ {$perawat->name}: Shift Pagi (Mon-Fri)");
        }

        // Perawat: Shift Siang, Senin-Jumat
        foreach ($perawatList->skip(2)->take(1) as $perawat) {
            WorkerShiftSchedule::setDefaultSchedule(
                $perawat->id,
                $shiftSiang->id,
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'Jadwal rutin shift siang'
            );
            $this->command->info("   ✅ {$perawat->name}: Shift Siang (Mon-Fri)");
        }

        // Dokter: Shift Pagi, Setiap Hari
        foreach ($dokterList->take(1) as $dokter) {
            WorkerShiftSchedule::setDefaultSchedule(
                $dokter->id,
                $shiftPagi->id,
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'Dokter jaga pagi setiap hari'
            );
            $this->command->info("   ✅ {$dokter->name}: Shift Pagi (Every Day)");
        }

        // Bidan: Shift Siang, Weekend
        foreach ($bidanList->take(1) as $bidan) {
            WorkerShiftSchedule::setDefaultSchedule(
                $bidan->id,
                $shiftSiang->id,
                ['saturday', 'sunday'],
                'Jadwal weekend shift siang'
            );
            $this->command->info("   ✅ {$bidan->name}: Shift Siang (Weekend)");
        }

        // Override examples
        $this->command->info('');
        $this->command->info('🔀 Creating override/exception schedules...');

        if ($perawatList->count() >= 2) {
            $perawat1 = $perawatList->first();
            $perawat2 = $perawatList->skip(1)->first();

            WorkerShiftSchedule::createOverride(
                $perawat1->id,
                $shiftSiang->id,
                Carbon::now()->addDays(20)->format('Y-m-d'),
                $perawat2->id,
                'Ganti shift karena rekan cuti'
            );
            $this->command->info("   ✅ Override: {$perawat1->name} ganti {$perawat2->name}");
        }

        $defaultCount = WorkerShiftSchedule::where('is_default', true)->count();
        $overrideCount = WorkerShiftSchedule::where('is_override', true)->count();

        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info("   Default schedules: {$defaultCount}");
        $this->command->info("   Override schedules: {$overrideCount}");
    }
}
