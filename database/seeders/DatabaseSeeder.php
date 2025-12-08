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
            PositionSeeder::class,           // 3️⃣
            LocationSeeder::class,           // 4️⃣
            DocumentTypeSeeder::class,       // 5️⃣
            
            // ========== SHIFT ==========
            ShiftSeeder::class,              // 6️⃣
            ShiftPatternSeeder::class,       // 7️⃣
            
            // ========== SPATIE PERMISSION ==========
            RolePermissionSeeder::class,     // 8️⃣
            
            // ========== WORKER & USER (BUTUH MASTER DATA) ==========
            WorkerSeeder::class,             // 9️⃣ (butuh Gender, Religion, Position)
            SuperAdminSeeder::class,         // 🔟 (butuh Worker, Role)
            UserSeeder::class,               // 1️⃣1️⃣ (butuh Worker, Role)
            
            // ========== SHIFT ASSIGNMENT (BUTUH WORKER & SHIFT) ==========
            // WorkerShiftAssignmentSeeder::class,  // 1️⃣2️⃣
            WorkerShiftScheduleSeeder::class,    // 1️⃣3️⃣
        ]);
    }
}
