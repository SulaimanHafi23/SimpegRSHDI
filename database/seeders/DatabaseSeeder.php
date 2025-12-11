<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // ========== MASTER DATA (HARUS DULUAN!) ==========
            ReligionSeeder::class,           // 1️⃣
            GenderSeeder::class,             // 2️⃣
            LocationSeeder::class,           // 3️⃣
            PositionSeeder::class,           // 4️⃣
            DocumentTypeSeeder::class,       // 5️⃣
            ShiftSeeder::class,              // 6️⃣
            ShiftPatternSeeder::class,       // 7️⃣

            // ========== SPATIE PERMISSION ==========
            RolePermissionSeeder::class,     // 8️⃣

            // ========== WORKER & USER (BUTUH MASTER DATA) ==========
            WorkerSeeder::class,             // 9️⃣ (butuh Gender, Religion, Position)
            UserSeeder::class,               // 1️⃣1️⃣ (butuh Worker, Role)
            SuperAdminSeeder::class,         // 🔟 (butuh Worker, Role)

            // ========== SHIFT ASSIGNMENT (BUTUH WORKER & SHIFT) ==========
            WorkerShiftScheduleSeeder::class,    // 1️⃣3️⃣
        ]);
    }
}
