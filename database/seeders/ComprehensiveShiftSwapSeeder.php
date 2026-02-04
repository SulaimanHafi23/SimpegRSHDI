<?php

namespace Database\Seeders;

use App\Models\ShiftSwapRequest;
use App\Models\Worker;
use App\Models\User;
use App\Models\WorkerShift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComprehensiveShiftSwapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive shift swap request data...');

        $workers = Worker::with(['workerShifts.shift', 'user'])->get()->filter(function($worker) {
            return $worker->workerShifts->isNotEmpty();
        });
        
        if ($workers->count() < 2) {
            $this->command->warn('Need at least 2 workers with shifts. Skipping shift swap seeder.');
            return;
        }

        $swapCount = 0;
        $statusDistribution = [
            'pending' => 15,        // 15%
            'accepted' => 25,       // 25%
            'awaiting_approval' => 25,  // 25%
            'approved' => 20,       // 20%
            'rejected' => 10,       // 10%
            'cancelled' => 5,       // 5%
        ];

        // Buat 80-120 shift swap requests (diperbanyak)
        $totalSwaps = rand(80, 120);

        for ($i = 0; $i < $totalSwaps; $i++) {
            $requestor = $workers->random();
            
            // Cari partner yang berbeda dan punya shift
            $partner = $workers->where('id', '!=', $requestor->id)
                               ->random();

            if ($partner) {
                $status = $this->getRandomStatus($statusDistribution);
                $this->createShiftSwapRequest($requestor, $partner, $status);
                $swapCount++;
            }
        }

        $this->command->info("✅ Created {$swapCount} shift swap requests with various scenarios!");
    }

    /**
     * Get random status based on distribution
     */
    private function getRandomStatus(array $distribution): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($distribution as $status => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'pending';
    }

    /**
     * Create shift swap request dengan detail
     */
    private function createShiftSwapRequest(Worker $requestor, Worker $partner, string $status): ShiftSwapRequest
    {
        // Random date dalam 2 bulan terakhir atau 1 bulan ke depan
        $baseDate = now()->subDays(rand(0, 60));
        
        // Untuk pending, gunakan tanggal future
        if ($status === 'pending') {
            $baseDate = now()->addDays(rand(3, 30));
        }

        // Get shifts
        $requestorShift = $requestor->workerShifts->first();
        $partnerShift = $partner->workerShifts->first();

        // Alasan swap yang beragam dan realistis
        $reasons = [
            'Ada keperluan keluarga mendesak',
            'Jadwal kuliah bentrok dengan shift',
            'Harus menghadiri acara pernikahan keluarga',
            'Ada janji dengan dokter spesialis',
            'Kondisi kesehatan kurang fit, perlu istirahat',
            'Ada kegiatan organisasi yang tidak bisa ditinggalkan',
            'Perlu mengurus dokumen penting di kantor pemerintahan',
            'Ada keperluan mendadak yang tidak bisa ditunda',
            'Ingin menghadiri wisuda adik',
            'Harus menjemput keluarga di bandara',
            'Ada training sertifikasi profesional',
            'Perlu menemani orang tua berobat ke rumah sakit',
            'Shift bentrok dengan acara keluarga besar',
            'Perlu menghadiri rapat OSIS/organisasi',
            'Ada keperluan dinas luar',
            'Perlu check up kesehatan rutin',
            'Harus menghadiri pemakaman keluarga',
            'Ada kelas tambahan yang tidak bisa dilewatkan',
            'Perlu mengantar anak sekolah pertama kali',
            'Ada interview pekerjaan sampingan',
            'Shift malam berturut-turut, perlu recovery',
            'Perlu menghadiri seminar wajib',
            'Ada acara keagamaan yang penting',
            'Harus mengurus perpanjangan SIM/STNK',
            'Perlu bantuan untuk moving house keluarga',
        ];

        $data = [
            'id' => Str::uuid(),
            'requester_id' => $requestor->id,
            'target_worker_id' => $partner->id,
            'requester_shift_id' => $requestorShift->id,
            'target_shift_id' => $partnerShift->id,
            'reason' => $reasons[array_rand($reasons)],
            'status' => $status,
            'requested_at' => $baseDate,
            'created_at' => $baseDate,
            'updated_at' => $baseDate,
        ];

        // Set requires_manager_approval
        $data['requires_manager_approval'] = in_array($status, ['awaiting_approval', 'approved']) ? 1 : 0;

        // Jika approved atau awaiting_approval, tambahkan data manager
        if (in_array($status, ['approved', 'awaiting_approval'])) {
            $manager = User::role(['Super Admin', 'HR', 'Manager'])->inRandomOrder()->first();
            $data['manager_id'] = $manager->id ?? null;
            
            if ($status === 'approved') {
                $data['manager_approved_at'] = $baseDate->copy()->addDays(rand(1, 2));
            }
        }

        // Jika executed (approved dan sudah dilaksanakan)
        if ($status === 'approved' && rand(1, 100) > 50) {
            $executor = User::role(['Super Admin', 'HR'])->inRandomOrder()->first();
            $data['executed_by'] = $executor->id ?? null;
            $data['executed_at'] = $baseDate->copy()->addDays(rand(3, 5));
        }

        // Set expires_at untuk pending/awaiting
        if (in_array($status, ['pending', 'awaiting_approval'])) {
            $data['expires_at'] = $baseDate->copy()->addDays(7);
        }

        return ShiftSwapRequest::create($data);
    }
}
