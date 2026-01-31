<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DepartmentDocumentTypeSeeder;

class SeedDepartmentDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:department-documents 
                            {--fresh : Clear existing mappings before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed document requirements for each department/position';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Running Department Document Type Seeder...');

        if ($this->option('fresh')) {
            $this->warn('⚠️  Using --fresh option: will clear existing mappings first');
            if (!$this->confirm('Are you sure you want to clear existing department-document mappings?')) {
                $this->info('❌ Cancelled by user');
                return;
            }
        }

        try {
            $seeder = new DepartmentDocumentTypeSeeder();
            $seeder->setCommand($this);
            $seeder->run();
            
            $this->info("\n✅ Department document requirements seeded successfully!");
            $this->info("📋 Use the admin panel at: /master/department-document-types");
            
        } catch (\Exception $e) {
            $this->error("❌ Error seeding department documents: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}