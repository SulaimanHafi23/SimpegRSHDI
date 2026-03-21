<?php

namespace App\Console\Commands;

use App\Models\WorkerDocument;
use App\Notifications\DocumentExpiryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDocumentExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:check-expiration
                            {--days=30 : Number of days in advance to check for expiration}
                            {--send : Actually send notifications (default: preview only)}
                            {--verbose : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring documents and send notifications to workers and managers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $shouldSend = $this->option('send');
        $verbose = $this->option('verbose');

        $this->info("Checking for documents expiring in the next {$days} days...");

        // Get all verified documents that will expire soon
        $expiringDocuments = WorkerDocument::query()
            ->whereNotNull('expired_date')
            ->where('status', 'verified')
            ->whereHas('worker', fn($q) => $q->where('status', 'active'))
            ->whereBetween('expired_date', [
                Carbon::today(),
                Carbon::today()->addDays($days)
            ])
            ->with(['worker', 'documentType'])
            ->orderBy('expired_date')
            ->get();

        $this->info("Found {$expiringDocuments->count()} expiring documents.");

        if ($expiringDocuments->isEmpty()) {
            $this->info('No expiring documents found.');
            return Command::SUCCESS;
        }

        // Group by urgency level
        $criticalDocs = [];    // ≤ 7 days
        $warningDocs = [];     // 8-14 days
        $normalDocs = [];      // 15+ days

        foreach ($expiringDocuments as $doc) {
            $daysUntilExpiry = $doc->expired_date->diffInDays(Carbon::today());

            if ($daysUntilExpiry <= 7) {
                $criticalDocs[] = $doc;
            } elseif ($daysUntilExpiry <= 14) {
                $warningDocs[] = $doc;
            } else {
                $normalDocs[] = $doc;
            }
        }

        // Display summary
        $this->table(
            ['Urgency Level', 'Count', 'Action'],
            [
                ['🔴 Critical (≤7 days)', count($criticalDocs), 'Send notifications'],
                ['⚠️  Warning (8-14 days)', count($warningDocs), 'Send notifications'],
                ['📋 Normal (15+ days)', count($normalDocs), 'Preview only'],
            ]
        );

        // Send notifications if --send flag is provided
        if ($shouldSend) {
            $notificationsSent = 0;

            // Send critical notifications
            foreach ($criticalDocs as $doc) {
                $this->sendNotificationForDocument($doc, 'critical');
                $notificationsSent++;

                if ($verbose) {
                    $this->line("  ✓ Critical notification sent to {$doc->worker->name} ({$doc->documentType->name})");
                }
            }

            // Send warning notifications
            foreach ($warningDocs as $doc) {
                $this->sendNotificationForDocument($doc, 'urgent');
                $notificationsSent++;

                if ($verbose) {
                    $this->line("  ✓ Warning notification sent to {$doc->worker->name} ({$doc->documentType->name})");
                }
            }

            // Send normal notifications (less frequent)
            // Only for documents expiring in exactly 30 days
            foreach ($normalDocs as $doc) {
                if ($doc->expired_date->diffInDays(Carbon::today()) === 30) {
                    $this->sendNotificationForDocument($doc, 'normal');
                    $notificationsSent++;

                    if ($verbose) {
                        $this->line("  ✓ Normal notification sent to {$doc->worker->name} ({$doc->documentType->name})");
                    }
                }
            }

            $this->info("✓ Successfully sent {$notificationsSent} notifications.");
        } else {
            $this->warning('Preview mode: No notifications sent.');
            $this->info('Use --send flag to actually send notifications.');

            if ($verbose) {
                $this->line("\nDocuments that would receive notifications:");
                foreach ($criticalDocs as $doc) {
                    $daysLeft = $doc->expired_date->diffInDays(Carbon::today());
                    $this->line("  🔴 {$doc->worker->name} - {$doc->documentType->name} (expires in {$daysLeft} days)");
                }
                foreach ($warningDocs as $doc) {
                    $daysLeft = $doc->expired_date->diffInDays(Carbon::today());
                    $this->line("  ⚠️  {$doc->worker->name} - {$doc->documentType->name} (expires in {$daysLeft} days)");
                }
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Send notification for a specific document
     */
    private function sendNotificationForDocument(WorkerDocument $doc, string $urgencyLevel): void
    {
        $daysUntilExpiry = $doc->expired_date->diffInDays(Carbon::today());

        $notification = new DocumentExpiryNotification($doc, $daysUntilExpiry, $urgencyLevel);

        // Send to worker
        if ($doc->worker && $doc->worker->user) {
            $doc->worker->user->notify($notification);
        }

        // Send to HR managers if critical
        if ($urgencyLevel === 'critical') {
            $this->notifyHRManagers($doc);
        }
    }

    /**
     * Notify HR managers about critical expiring documents
     */
    private function notifyHRManagers(WorkerDocument $doc): void
    {
        // Get users with HR role or permission to manage documents
        $hrManagers = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'like', '%HR%')
            ->select('users.*')
            ->distinct()
            ->get();

        foreach ($hrManagers as $manager) {
            if ($manager && class_exists('App\Models\User')) {
                $user = \App\Models\User::find($manager->id);
                if ($user) {
                    $daysUntilExpiry = $doc->expired_date->diffInDays(Carbon::today());
                    $user->notify(new DocumentExpiryNotification($doc, $daysUntilExpiry, 'critical'));
                }
            }
        }
    }
}
