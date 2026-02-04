<?php

namespace Database\Seeders;

use App\Models\BusinessTrip;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnhancedBusinessTripSeeder extends Seeder
{
    /**
     * Run the database seeds dengan berbagai status dan skenario
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive business trip data...');

        $workers = Worker::all();

        if ($workers->isEmpty()) {
            $this->command->warn('No workers found!');
            return;
        }

        $statuses = [
            'pending' => 20,          // 20% pending
            'approved' => 40,         // 40% approved
            'rejected' => 15,         // 15% rejected
            'on_trip' => 15,          // 15% sedang perjalanan
            'completed' => 10,        // 10% completed
        ];

        foreach ($workers as $worker) {
            // Setiap worker punya 1-4 business trips
            $tripCount = rand(1, 4);

            for ($i = 0; $i < $tripCount; $i++) {
                $status = $this->getRandomStatus($statuses);
                
                $trip = $this->createBusinessTrip($worker, $status);

                $this->command->info("Created {$status} trip for {$worker->name}: {$trip->destination}");
            }
        }

        $this->command->info('✅ Enhanced business trip data created successfully!');
    }

    /**
     * Get random status berdasarkan weight
     */
    private function getRandomStatus(array $statuses): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($statuses as $status => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'pending';
    }

    /**
     * Create business trip dengan detail
     */
    private function createBusinessTrip(Worker $worker, string $status)
    {
        $now = Carbon::now();

        // Generate dates berdasarkan status
        switch ($status) {
            case 'pending':
                // Future trips (5-90 hari ke depan)
                $startDate = $now->copy()->addDays(rand(5, 90));
                break;

            case 'approved':
                // Future trips (1-60 hari ke depan)
                $startDate = $now->copy()->addDays(rand(1, 60));
                break;

            case 'rejected':
                // Past requests
                $startDate = $now->copy()->subDays(rand(5, 60));
                break;

            case 'on_trip':
                // Current trips (started 0-5 hari lalu)
                $startDate = $now->copy()->subDays(rand(0, 5));
                break;

            case 'completed':
                // Past trips (ended 1-90 hari lalu)
                $endDatePast = $now->copy()->subDays(rand(1, 90));
                $duration = rand(1, 14);
                $startDate = $endDatePast->copy()->subDays($duration);
                break;

            default:
                $startDate = $now->copy()->addDays(rand(5, 30));
        }

        // Duration: 1-14 hari
        $duration = rand(1, 14);
        $endDate = $startDate->copy()->addDays($duration);

        $destinations = [
            'Jakarta',
            'Surabaya',
            'Bandung',
            'Medan',
            'Semarang',
            'Makassar',
            'Palembang',
            'Denpasar, Bali',
            'Yogyakarta',
            'Balikpapan',
            'Pontianak',
            'Batam',
            'Manado',
            'Solo',
            'Malang',
            'Singapore',
            'Kuala Lumpur, Malaysia',
            'Bangkok, Thailand',
        ];

        $purposes = [
            'Meeting dengan client',
            'Training dan workshop',
            'Audit cabang',
            'Presentasi proyek',
            'Kunjungan vendor',
            'Implementasi sistem',
            'Koordinasi dengan partner',
            'Site visit proyek',
            'Seminar nasional',
            'Konferensi industri',
            'Negosiasi kontrak',
            'Peresmian kantor cabang',
            'Quality assurance check',
            'Market research',
            'Customer visit',
        ];

        $transportations = [
            'Pesawat',
            'Kereta Api',
            'Bus',
            'Mobil Dinas',
            'Rental Car',
        ];

        $accommodations = [
            'Hotel Bintang 3',
            'Hotel Bintang 4',
            'Hotel Bintang 5',
            'Guest House',
            'Apartemen',
        ];

        $rejectionReasons = [
            'Budget perjalanan dinas bulan ini sudah melebihi alokasi',
            'Perjalanan tidak urgent, bisa dijadwalkan ulang',
            'Bisa dilakukan secara virtual/online',
            'Perlu konfirmasi lebih lanjut dengan pihak terkait',
            'Timing tidak tepat dengan jadwal proyek',
            'Destinasi perlu dipertimbangkan ulang',
        ];

        // Generate budget (1.000.000 - 15.000.000)
        $estimatedBudget = rand(1000000, 15000000);
        $estimatedBudget = round($estimatedBudget / 100000) * 100000; // Round to 100k

        $data = [
            'id' => Str::uuid(),
            'worker_id' => $worker->id,
            'destination' => $destinations[array_rand($destinations)],
            'purpose' => $purposes[array_rand($purposes)],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'transportation' => $transportations[array_rand($transportations)],
            'accommodation' => $accommodations[array_rand($accommodations)],
            'estimated_budget' => $estimatedBudget,
            'status' => $status,
            'created_at' => $startDate->copy()->subDays(rand(7, 30)),
        ];

        // Add approval details
        if (in_array($status, ['approved', 'on_trip', 'completed'])) {
            $approver = \App\Models\User::role(['Manager', 'HR', 'Super Admin'])->inRandomOrder()->first();
            
            if ($approver) {
                $data['approved_by'] = $approver->id;
                $data['approved_at'] = $data['created_at']->copy()->addDays(rand(1, 3));
                $data['approval_notes'] = 'Disetujui untuk keperluan operasional';
            }
        }

        // Add rejection details
        if ($status === 'rejected') {
            $rejector = \App\Models\User::role(['Manager', 'HR', 'Super Admin'])->inRandomOrder()->first();
            
            if ($rejector) {
                $data['approved_by'] = $rejector->id;
                $data['approved_at'] = $data['created_at']->copy()->addDays(rand(1, 2));
                $data['rejection_reason'] = $rejectionReasons[array_rand($rejectionReasons)];
            }
        }

        // Add completion details
        if ($status === 'completed') {
            $data['actual_start_date'] = $startDate;
            $data['actual_end_date'] = $endDate;
            
            // Actual budget bisa lebih atau kurang dari estimasi (80% - 120%)
            $variance = rand(80, 120) / 100;
            $data['actual_budget'] = round($estimatedBudget * $variance / 100000) * 100000;
            
            $data['completed_at'] = $endDate->copy()->addDays(1);
            $data['trip_report'] = 'Perjalanan dinas telah diselesaikan dengan baik. Semua tujuan tercapai dan meeting dengan pihak terkait berjalan lancar.';
        }

        // Add notes (50% kemungkinan)
        if (rand(1, 100) <= 50) {
            $notes = [
                'Koordinasi dengan tim lokal diperlukan',
                'Bawa laptop dan dokumen presentasi',
                'Meeting dijadwalkan pukul 09:00',
                'Perlu konfirmasi booking hotel',
                'Dokumen perjalanan sudah disiapkan',
                'Tim terdiri dari 2-3 orang',
            ];
            $data['notes'] = $notes[array_rand($notes)];
        }

        return BusinessTrip::create($data);
    }
}
