<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Notifications...');

        $users = User::all();

        $notificationTypes = [
            [
                'type' => 'App\\Notifications\\LeaveRequestApproved',
                'title' => 'Pengajuan Cuti Disetujui',
                'message' => 'Pengajuan cuti Anda telah disetujui oleh atasan.',
            ],
            [
                'type' => 'App\\Notifications\\LeaveRequestRejected',
                'title' => 'Pengajuan Cuti Ditolak',
                'message' => 'Pengajuan cuti Anda telah ditolak. Silakan hubungi HR untuk informasi lebih lanjut.',
            ],
            [
                'type' => 'App\\Notifications\\OvertimeApproved',
                'title' => 'Lembur Disetujui',
                'message' => 'Pengajuan lembur Anda telah disetujui.',
            ],
            [
                'type' => 'App\\Notifications\\BusinessTripApproved',
                'title' => 'Perjalanan Dinas Disetujui',
                'message' => 'Pengajuan perjalanan dinas Anda telah disetujui. Silakan persiapkan keberangkatan.',
            ],
            [
                'type' => 'App\\Notifications\\ShiftSwapApproved',
                'title' => 'Pertukaran Shift Disetujui',
                'message' => 'Permintaan pertukaran shift Anda telah disetujui.',
            ],
            [
                'type' => 'App\\Notifications\\DocumentVerified',
                'title' => 'Dokumen Terverifikasi',
                'message' => 'Dokumen yang Anda upload telah diverifikasi.',
            ],
            [
                'type' => 'App\\Notifications\\PayrollGenerated',
                'title' => 'Slip Gaji Tersedia',
                'message' => 'Slip gaji bulan ini telah tersedia. Silakan cek di menu Penggajian.',
            ],
            [
                'type' => 'App\\Notifications\\ShiftReminder',
                'title' => 'Pengingat Shift',
                'message' => 'Anda memiliki shift besok. Jangan lupa untuk hadir tepat waktu.',
            ],
        ];

        foreach ($users as $user) {
            // Generate 5-10 notifications per user
            $count = rand(5, 10);

            for ($i = 0; $i < $count; $i++) {
                $notif = $notificationTypes[array_rand($notificationTypes)];
                $createdAt = Carbon::now()->subDays(rand(1, 30));

                // 60% chance notification is read
                $isRead = rand(1, 100) <= 60;

                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => $notif['type'],
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => $notif['title'],
                        'message' => $notif['message'],
                        'action_url' => null,
                    ]),
                    'read_at' => $isRead ? $createdAt->copy()->addHours(rand(1, 48)) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $this->command->info('✅ Notifications seeded successfully!');
    }
}
