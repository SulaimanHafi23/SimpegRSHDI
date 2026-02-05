<?php

namespace Database\Seeders;

use App\Models\OvertimeRequest;
use App\Models\Worker;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComprehensiveOvertimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive overtime request data...');

        $workers = Worker::with('user')->get();
        
        if ($workers->isEmpty()) {
            $this->command->warn('No workers found. Please seed workers first.');
            return;
        }

        $overtimeCount = 0;
        $statusDistribution = [
            'pending' => 30,    // 30%
            'approved' => 55,   // 55%
            'rejected' => 15,   // 15%
        ];

        foreach ($workers as $worker) {
            // Setiap worker punya 3-6 overtime requests
            $numOvertimes = rand(3, 6);

            for ($i = 0; $i < $numOvertimes; $i++) {
                $status = $this->getRandomStatus($statusDistribution);
                $overtime = $this->createOvertimeRequest($worker, $status);
                $overtimeCount++;
            }
        }

        $this->command->info("✅ Created {$overtimeCount} overtime requests with various scenarios!");
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
     * Create overtime request dengan detail
     */
    private function createOvertimeRequest(Worker $worker, string $status): OvertimeRequest
    {
        // Random date dalam 3 bulan terakhir atau 1 bulan ke depan
        $baseDate = now()->subDays(rand(0, 90));
        
        // Untuk pending dan approved yang belum terjadi, gunakan tanggal future
        if ($status === 'pending' && rand(1, 100) > 50) {
            $baseDate = now()->addDays(rand(1, 30));
        }

        $overtimeDate = $baseDate->format('Y-m-d');

        // Random duration (2-8 jam)
        $duration = rand(2, 8);
        $startHour = rand(17, 20); // 17:00 - 20:00
        $startTime = sprintf('%02d:00:00', $startHour);
        
        // Hitung end time, jika melewati 24:00 set ke 23:59:59
        $endHour = $startHour + $duration;
        if ($endHour >= 24) {
            $endTime = '23:59:59';
            $duration = 24 - $startHour; // Adjust duration
        } else {
            $endTime = sprintf('%02d:00:00', $endHour);
        }

        // Keperluan lembur yang beragam
        $purposes = [
            'Menyelesaikan laporan akhir bulan',
            'Maintenance sistem database',
            'Persiapan audit internal',
            'Handling pasien emergency',
            'Backup data server',
            'Training karyawan baru',
            'Inventarisasi obat dan alkes',
            'Persiapan akreditasi rumah sakit',
            'Update sistem informasi manajemen',
            'Koordinasi dengan vendor',
            'Penyelesaian dokumen medis',
            'Rapat evaluasi kinerja',
            'Penyusunan SOP baru',
            'Quality control laboratorium',
            'Sterilisasi alat medis tambahan',
        ];

        $data = [
            'id' => Str::uuid(),
            'worker_id' => $worker->id,
            'overtime_date' => $overtimeDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'total_hours' => $duration,
            'reason' => $purposes[array_rand($purposes)],
            'status' => $status,
            'created_at' => $baseDate,
            'updated_at' => $baseDate,
        ];

        // Jika approved atau rejected, tambahkan data approval
        if (in_array($status, ['approved', 'rejected'])) {
            // Get random admin/manager untuk approver
            $approver = User::role(['Super Admin', 'HR', 'Manager'])->inRandomOrder()->first();
            
            $data['approved_by'] = $approver->id ?? null;
            $data['approved_at'] = $baseDate->copy()->addDays(rand(1, 3));

            if ($status === 'rejected') {
                $rejectionReasons = [
                    'Tidak ada budget lembur bulan ini',
                    'Pekerjaan dapat diselesaikan pada jam kerja normal',
                    'Sudah ada karyawan lain yang standby',
                    'Tidak sesuai dengan kebijakan perusahaan',
                    'Kuota lembur bulan ini sudah penuh',
                ];
                $data['rejection_reason'] = $rejectionReasons[array_rand($rejectionReasons)];
            }
        }

        return OvertimeRequest::create($data);
    }
}
