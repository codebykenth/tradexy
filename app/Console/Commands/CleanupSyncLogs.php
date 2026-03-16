<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

final class CleanupSyncLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete sync logs older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Cleaning up old sync logs (24h threshold)...');

            // Purge high-frequency sync logs older than 24 hours
            $syncActions = [
                'bybit_sync',
                'bybit_sync_failed',
                'bybit_balance_sync',
                'bybit_balance_failed',
                'system_cleanup',
                'daily_news_generated',
            ];

            $syncDeleted = ActivityLog::whereIn('action', $syncActions)
                ->where('created_at', '<', now()->subDay())
                ->delete();

            $this->info("Purged {$syncDeleted} sync logs.");

            // Purge general audit logs (created/updated/deleted) older than 30 days
            $this->info('Cleaning up old audit logs (30d threshold)...');
            $auditDeleted = ActivityLog::where('created_at', '<', now()->subDays(30))
                ->delete();

            $this->info("Purged {$auditDeleted} old audit logs.");

            // Log the cleanup action itself for audit visibility
            ActivityLog::create([
                'action' => 'system_cleanup',
                'description' => "System maintenance completed. Sync logs purged: {$syncDeleted}. Audit logs purged: {$auditDeleted}.",
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error("Logs cleanup failed: {$e->getMessage()}");

            $email = config('services.bybit.user_email');
            if ($email) {
                \Illuminate\Support\Facades\Mail::to($email)->send(
                    new \App\Mail\Errors\GenericJobFailedMail('Logs Cleanup Task', $e->getMessage())
                );
            }

            return 1;
        }
    }
}
