<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Backup database to Firebase Storage and cleanup old files.';

    public function handle(): int
    {
        $this->info('Starting database backup to Firebase...');

        $env = config('app.env', 'local');
        $filename = "{$env}-backup-".now()->format('Y-m-d_His').'.sql';
        $tempPath = storage_path("app/temp-{$filename}");

        // 1. Generate SQL dump locally
        $config = config('database.connections.pgsql');
        $dsn = $config['url'] ?? sprintf(
            'postgresql://%s:%s@%s:%s/%s',
            $config['username'],
            $config['password'],
            $config['host'],
            $config['port'],
            $config['database']
        );

        $command = sprintf('pg_dump --dbname=%s > %s', escapeshellarg($dsn), escapeshellarg($tempPath));

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Failed to generate SQL dump.');

            return 1;
        }

        // 2. Upload to Firebase
        try {
            $prefix = ($env === 'production' || $env === 'prod') ? '' : 'test/';
            $this->info("Uploading to Google Cloud Storage ({$prefix}backups folder)...");
            $remotePath = "{$prefix}backups/{$filename}";
            Storage::disk('gcs')->put($remotePath, fopen($tempPath, 'r+'));
            $this->info("Backup successfully uploaded: {$remotePath}");
        } catch (\Exception $e) {
            ActivityLog::create([
                'action' => 'db_backup_failed',
                'description' => 'Database backup failed: '.substr($e->getMessage(), 0, 200),
            ]);
            $this->error('Firebase Upload failed: '.$e->getMessage());

            $email = config('services.bybit.user_email');
            if ($email) {
                \Illuminate\Support\Facades\Mail::to($email)->send(
                    new \App\Mail\Errors\GenericJobFailedMail('Database Backup Task', $e->getMessage())
                );
            }

            File::delete($tempPath);

            return 1;
        }

        // 3. Cleanup local temp file
        File::delete($tempPath);

        // 4. Cleanup Firebase backups older than 7 days
        $this->cleanupFirebaseBackups($env);

        ActivityLog::create([
            'action' => 'db_backup_success',
            'description' => "Database backup successfully uploaded to GCS: {$filename}",
        ]);

        return 0;
    }

    private function cleanupFirebaseBackups(string $env): void
    {
        $prefix = ($env === 'production' || $env === 'prod') ? '' : 'test/';
        $this->info("Checking for old {$env} backups in GCS...");

        $disk = Storage::disk('gcs');
        $files = $disk->files("{$prefix}backups"); // Only fetch files in the environment folder
        $threshold = now()->subDays(7)->getTimestamp();

        foreach ($files as $file) {
            if ($disk->lastModified($file) < $threshold) {
                $disk->delete($file);
                $this->info("Deleted old Firebase backup: {$file}");
            }
        }
    }
}
