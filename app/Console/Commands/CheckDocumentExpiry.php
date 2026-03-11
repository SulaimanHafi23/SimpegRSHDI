<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DocumentExpiryNotification;
use App\Services\Document\DocumentExpiryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDocumentExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:check-expiry
                            {--dry-run : Run without sending notifications}
                            {--force : Send notifications even if already sent today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring documents and send notifications to HR and employees';

    protected $documentExpiryService;

    /**
     * Create a new command instance.
     */
    public function __construct(DocumentExpiryService $documentExpiryService)
    {
        parent::__construct();
        $this->documentExpiryService = $documentExpiryService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info('🔍 Checking for expiring documents...');
        $this->newLine();

        // Get documents that need notification
        $documents = $this->documentExpiryService->getDocumentsNeedingNotification();

        if ($documents->isEmpty()) {
            $this->info('✅ No documents need notification at this time.');
            return Command::SUCCESS;
        }

        $this->info("Found {$documents->count()} document(s) that need notification:");
        $this->newLine();

        // Get HR users who should receive notifications
        $hrUsers = User::role(['HR', 'Super Admin'])->where('is_active', true)->get();

        if ($hrUsers->isEmpty()) {
            $this->warn('⚠️  No active HR users found to send notifications to.');
            Log::warning('Document expiry check: No HR users found');
        }

        $notificationsSent = 0;
        $notificationsFailed = 0;

        foreach ($documents as $document) {
            $daysUntilExpiry = $this->documentExpiryService->getDaysUntilExpiry($document);
            $urgencyLevel = $this->documentExpiryService->getUrgencyLevel($document);

            $documentName = $document->documentType?->name
                ?? $document->departmentDocumentType?->customDocumentType?->name
                ?? 'Dokumen';

            $workerName = $document->worker->name;
            $expiryDate = $document->expired_date->format('d/m/Y');

            // Display document info
            $urgencyIcon = $this->getUrgencyIcon($urgencyLevel);
            $this->line("{$urgencyIcon} {$documentName} - {$workerName}");
            $this->line("   Expires: {$expiryDate} ({$daysUntilExpiry} days)");

            if ($isDryRun) {
                $this->line("   [DRY RUN] Would send notification to HR and employee");
                $this->newLine();
                continue;
            }

            try {
                // Notify HR users
                foreach ($hrUsers as $hrUser) {
                    $hrUser->notify(new DocumentExpiryNotification($document, $daysUntilExpiry, $urgencyLevel));
                }

                // Notify the employee (document owner)
                if ($document->worker->user && $document->worker->user->is_active) {
                    $document->worker->user->notify(new DocumentExpiryNotification($document, $daysUntilExpiry, $urgencyLevel));
                    $this->line("   ✅ Notification sent to HR ({$hrUsers->count()}) and employee");
                } else {
                    $this->line("   ⚠️  Notification sent to HR ({$hrUsers->count()}) only (employee user not found/inactive)");
                }

                $notificationsSent++;

                Log::info('Document expiry notification sent', [
                    'document_id' => $document->id,
                    'document_name' => $documentName,
                    'worker_id' => $document->worker_id,
                    'worker_name' => $workerName,
                    'days_until_expiry' => $daysUntilExpiry,
                    'urgency_level' => $urgencyLevel,
                ]);

            } catch (\Exception $e) {
                $this->error("   ❌ Failed to send notification: {$e->getMessage()}");
                $notificationsFailed++;

                Log::error('Failed to send document expiry notification', [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->newLine();
        }

        // Summary
        $this->newLine();
        $this->info('📊 Summary:');
        $this->line("   Total documents checked: {$documents->count()}");

        if (!$isDryRun) {
            $this->line("   Notifications sent: {$notificationsSent}");
            if ($notificationsFailed > 0) {
                $this->line("   Notifications failed: {$notificationsFailed}");
            }
        }

        // Show statistics
        $this->newLine();
        $this->info('📈 Overall Document Expiry Statistics:');
        $stats = $this->documentExpiryService->getExpiryStatistics();
        $this->line("   Expired: {$stats['expired']}");
        $this->line("   Expiring in 30 days: {$stats['expiring_30_days']}");
        $this->line("   Expiring in 60 days: {$stats['expiring_60_days']}");
        $this->line("   Expiring in 90 days: {$stats['expiring_90_days']}");

        if ($isDryRun) {
            $this->newLine();
            $this->info('🏁 Dry run completed. No notifications were sent.');
        } else {
            $this->newLine();
            $this->info('✅ Document expiry check completed successfully.');
        }

        return Command::SUCCESS;
    }

    /**
     * Get icon based on urgency level.
     */
    private function getUrgencyIcon(string $urgency): string
    {
        return match ($urgency) {
            'critical' => '🔴',
            'urgent' => '🟠',
            'warning' => '🟡',
            'watch' => '🔵',
            default => '⚪',
        };
    }
}
