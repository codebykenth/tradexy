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
        $this->info('Cleaning up old sync logs...');

        $deleted = ActivityLog::whereIn('action', ['bybit_sync', 'bybit_sync_failed'])
            ->where('created_at', '<', now()->subDay())
            ->delete();

        $this->info("Deleted {$deleted} log entries.");

        return 0;
    }
}
