<?php

namespace Database\Seeders;

use App\Models\BusinessTrip;
use App\Models\Worker;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComprehensiveBusinessTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive business trip data...');

        $workers = Worker::with('user')->get();

        if ($workers->isEmpty()) {
            $this->command->warn('No workers found. Please seed workers first.');
            return;
        }

        $tripCount = 0;
        $statusDistribution = [
            'pending' => 30,     // 30%
            'approved' => 50,    // 50%
            'rejected' => 15,    // 15%
            'cancelled' => 5,    // 5%
        ];

        foreach ($workers as $worker) {
            // Setiap worker punya 2-5 business trips
            $numTrips = rand(2, 5);

            for ($i = 0; $i < $numTrips; $i++) {
                $status = $this->getRandomStatus($statusDistribution);
                $this->createBusinessTrip($worker, $status);
                $tripCount++;
            }
        }

        $this->command->info("✅ Created {$tripCount} business trips with various scenarios!");
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
     * Create business trip dengan detail
     */
    private function createBusinessTrip(Worker $worker, string $status): BusinessTrip
    {
        // Tujuan perjalanan dinas yang beragam
        $destinations = [
            'Jakarta', 'Surabaya', 'Bandung', 'Yogyakarta', 'Semarang',
            'Bali', 'Medan', 'Makassar', 'Palembang', 'Balikpapan',
            'Manado', 'Batam', 'Malang', 'Solo', 'Padang',
            'Banjarmasin', 'Pontianak', 'Samarinda', 'Pekanbaru', 'Jambi'
        ];

        // Keperluan perjalanan dinas yang beragam
        $purposes = [
            'Meeting dengan stakeholder dan presentasi project',
            'Training dan workshop pengembangan SDM',
            'Koordinasi dengan cabang regional',
            'Site visit dan evaluasi fasilitas',
            'Attending medical conference dan seminar',
            'Vendor assessment dan negosiasi kontrak',
            'Quality audit dan compliance check',
            'Business development dan ekspansi',
            'Technical support dan troubleshooting',
            'Strategic planning meeting',
            'Customer visit dan relationship building',
            'Knowledge sharing session',
            'Installation dan commissioning equipment',
            'Benchmarking study ke rumah sakit lain',
            'Supplier meeting dan procurement',
        ];

        // Transportasi yang beragam
        $transportations = [
            'Pesawat',
            'Kereta Api',
            'Mobil Dinas',
            'Bus/Travel',
            'Pesawat + Mobil Rental',
        ];

        // Akomodasi
        $accommodations = [
            'Hotel Bintang 3',
            'Hotel Bintang 4',
            'Hotel Bintang 5',
            'Guest House',
            'Apartemen Sewa',
            null, // Tidak menginap
        ];

        // Random date
        $baseDate = now()->subDays(rand(0, 60));

        // Untuk pending, gunakan tanggal future (minimal 2 hari ke depan)
        if ($status === 'pending') {
            $baseDate = now()->addDays(rand(2, 45));
        }

        // Untuk completed, gunakan tanggal past
        if ($status === 'completed') {
            $baseDate = now()->subDays(rand(10, 60));
        }

        // Duration (1-7 hari)
        $duration = rand(1, 7);
        $startDate = $baseDate->copy()->format('Y-m-d');
        $endDate = $baseDate->copy()->addDays($duration)->format('Y-m-d');

        // Estimasi biaya berdasarkan durasi dan transportasi
        $baseCost = rand(2000000, 5000000);
        $estimatedCost = $baseCost + ($duration * rand(500000, 1500000));

        $transportation = $transportations[array_rand($transportations)];
        $accommodation = $accommodations[array_rand($accommodations)];

        $data = [
            'id' => Str::uuid(),
            'worker_id' => $worker->id,
            'destination' => $destinations[array_rand($destinations)],
            'purpose' => $purposes[array_rand($purposes)],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'estimated_cost' => $estimatedCost,
            'transportation' => $transportation,
            'accommodation' => $accommodation,
            'status' => $status,
            'created_at' => $baseDate->copy()->subDays(rand(3, 10)),
            'updated_at' => $baseDate->copy()->subDays(rand(1, 3)),
        ];

        // Jika approved, rejected, atau completed, tambahkan data approval
        if (in_array($status, ['approved', 'rejected', 'completed'])) {
            // Get random admin/manager untuk approver
            $approver = User::role(['Super Admin', 'HR', 'Manager'])->inRandomOrder()->first();

            $data['approved_by'] = $approver->id ?? null;
            $data['approved_at'] = $baseDate->copy()->subDays(rand(1, 5));

            if ($status === 'rejected') {
                $rejectionReasons = [
                    'Budget perjalanan dinas bulan ini sudah melebihi alokasi',
                    'Tidak urgent, dapat dilakukan via online meeting',
                    'Sudah ada perwakilan dari departemen lain yang hadir',
                    'Timing tidak tepat, ada kegiatan prioritas di kantor',
                    'Biaya terlalu tinggi, perlu optimasi budget',
                    'Kuota perjalanan dinas bulan ini sudah penuh',
                ];
                $data['rejection_reason'] = $rejectionReasons[array_rand($rejectionReasons)];
            }
        }

        // Jika cancelled
        if ($status === 'cancelled') {
            $data['approved_by'] = $worker->user->id ?? null;
            $data['approved_at'] = $baseDate->copy()->subDays(rand(1, 2));
            $data['rejection_reason'] = 'Dibatalkan karena perubahan agenda';
        }

        // Add notes untuk beberapa trip
        if (rand(1, 100) > 60) {
            $notes = [
                'Mohon dibantu booking tiket dan hotel',
                'Perlu approval urgent',
                'Sudah dikonfirmasi oleh pihak tujuan',
                'Bersama delegasi 3 orang',
                'Termasuk budget untuk transport lokal',
            ];
            $data['notes'] = $notes[array_rand($notes)];
        }

        return BusinessTrip::create($data);
    }
}
