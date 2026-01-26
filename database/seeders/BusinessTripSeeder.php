<?php

namespace Database\Seeders;

use App\Models\BusinessTrip;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BusinessTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Business Trips...');

        $workers = Worker::with('user')->get();
        $approvers = User::role(['Super Admin', 'HR', 'Manager'])->get();

        $destinations = [
            'Jakarta',
            'Surabaya',
            'Bandung',
            'Yogyakarta',
            'Semarang',
            'Medan',
            'Makassar',
            'Bali',
        ];

        $purposes = [
            'Kunjungan ke rumah sakit cabang untuk evaluasi layanan kesehatan',
            'Pelatihan manajemen rumah sakit tingkat nasional',
            'Koordinasi dengan Dinas Kesehatan regional',
            'Audit medis dan administrasi',
            'Seminar nasional kesehatan',
            'Kunjungan supplier alat medis',
            'Workshop standar operasional prosedur rumah sakit',
            'Meeting dengan stakeholder proyek kesehatan',
        ];

        $transportations = ['Pesawat', 'Kereta Api', 'Mobil Dinas', 'Bus'];
        $accommodations = ['Hotel Bintang 3', 'Hotel Bintang 4', 'Guesthouse', 'Penginapan'];
        $statuses = ['pending', 'approved', 'rejected', 'cancelled'];

        foreach ($workers as $worker) {
            // Generate 1-3 business trips per worker
            $count = rand(1, 3);

            for ($i = 0; $i < $count; $i++) {
                $startDate = Carbon::now()->addDays(rand(-60, 60));
                $duration = rand(1, 5);
                $endDate = $startDate->copy()->addDays($duration);
                $status = $statuses[array_rand($statuses)];

                $businessTrip = BusinessTrip::create([
                    'worker_id' => $worker->id,
                    'destination' => $destinations[array_rand($destinations)],
                    'purpose' => $purposes[array_rand($purposes)],
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'transportation' => $transportations[array_rand($transportations)],
                    'accommodation' => $accommodations[array_rand($accommodations)],
                    'estimated_cost' => rand(2000000, 10000000),
                    'status' => $status,
                    'notes' => $i % 3 == 0 ? 'Perjalanan dinas dalam rangka peningkatan kualitas layanan' : null,
                ]);

                // If approved or rejected, add approver info
                if (in_array($status, ['approved', 'rejected']) && $approvers->isNotEmpty()) {
                    $approver = $approvers->random();
                    $businessTrip->update([
                        'approved_by' => $approver->id,
                        'approved_at' => $startDate->copy()->subDays(rand(3, 10)),
                    ]);

                    if ($status === 'rejected') {
                        $businessTrip->update([
                            'rejection_reason' => 'Budget perjalanan dinas untuk bulan ini sudah habis',
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ Business Trips seeded successfully!');
    }
}
