<?php

declare(strict_types=1);

namespace App\Console\Commands;

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
            $this->info("Uploading to Firebase Storage ({$env} folder)...");
            $remotePath = "{$env}/{$filename}";
            Storage::disk('firebase')->put($remotePath, fopen($tempPath, 'r+'));
            $this->info("Backup successfully uploaded: {$remotePath}");
        } catch (\Exception $e) {
            $this->error('Firebase Upload failed: '.$e->getMessage());
            File::delete($tempPath);

            return 1;
        }

        // 3. Cleanup local temp file
        File::delete($tempPath);

        // 4. Cleanup Firebase backups older than 7 days
        $this->cleanupFirebaseBackups($env);

        return 0;
    }

    private function cleanupFirebaseBackups(string $env): void
    {
        $this->info("Checking for old {$env} backups in Firebase...");

        $disk = Storage::disk('firebase');
        $files = $disk->files($env); // Only fetch files in the environment folder
        $threshold = now()->subDays(7)->getTimestamp();

        foreach ($files as $file) {
            if ($disk->lastModified($file) < $threshold) {
                $disk->delete($file);
                $this->info("Deleted old Firebase backup: {$file}");
            }
        }
    }
}
