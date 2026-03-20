<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Trade;
use App\Services\FileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class OptimizeExistingImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trades:optimize-images {--limit=50 : Number of trades to process at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shrink and compress existing trade chart images to improve LCP';

    public function __construct(
        private readonly FileService $fileService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting existing image optimization...');

        $trades = Trade::whereNotNull('chart_picture')
            ->where('chart_picture', 'like', 'http%')
            ->latest()
            ->take((int) $this->option('limit'))
            ->get();

        if ($trades->isEmpty()) {
            $this->info('No trades with images found.');

            return 0;
        }

        $bar = $this->output->createProgressBar($trades->count());
        $bar->start();

        foreach ($trades as $trade) {
            try {
                $this->optimizeTradeImage($trade);
            } catch (\Exception $e) {
                $this->error("\nError processing trade #{$trade->id}: ".$e->getMessage());
                // For debugging local connection issues:
                $this->line($e->getTraceAsString());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Optimization complete! Run again to process more if needed.');

        return 0;
    }

    private function optimizeTradeImage(Trade $trade): void
    {
        $url = $trade->chart_picture;
        $fileName = $this->fileService->getFileNameFromUrl($url);

        // Skip if not from GCS
        if (!Str::contains($url, 'storage.googleapis.com') && !Str::contains($url, 'tradexy')) {
            return;
        }

        $bucket = config('filesystems.disks.gcs.bucket');

        // Try both production and test paths to handle local vs prod environment differences
        $pathsToTry = [
            "users/{$trade->user_id}/trades/{$trade->id}/{$fileName}",
            "test/users/{$trade->user_id}/trades/{$trade->id}/{$fileName}",
            "users/{$trade->user_id}/trades/{$fileName}",
            "test/users/{$trade->user_id}/trades/{$fileName}",
        ];

        $foundPath = null;
        foreach ($pathsToTry as $testPath) {
            try {
                if (Storage::disk('gcs')->exists($testPath)) {
                    $foundPath = $testPath;

                    break;
                }
            } catch (\Exception $e) {
                // Ignore individual connection errors
                continue;
            }
        }

        if (!$foundPath) {
            $this->error("\n[Trade #{$trade->id}] Image not found in bucket [{$bucket}] at any expected path.");

            return;
        }

        // 2. Download to local temp
        $tempPath = storage_path('app/temp_optimize_'.Str::random(10));
        $content = Storage::disk('gcs')->get($foundPath);
        file_put_contents($tempPath, $content);

        $this->info("\nProcessing trade #{$trade->id} ({$fileName})...");

        // 3. Process image (Resize + Compress)
        $this->processLocalFile($tempPath);

        // 4. Upload back (Overwrite original)
        Storage::disk('gcs')->put($foundPath, file_get_contents($tempPath), [
            'visibility' => 'public',
            'metadata' => [
                'cacheControl' => 'public, max-age=31536000',
            ],
        ]);

        // 5. Cleanup
        unlink($tempPath);
    }

    private function processLocalFile(string $path): void
    {
        $info = getimagesize($path);
        if (!$info) {
            return;
        }

        $mime = $info['mime'];
        [$width, $height] = $info;

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $image = @imagecreatefromjpeg($path);
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($path);
        } else {
            return;
        }

        if (!$image) {
            return;
        }

        // Resize if wider than 1400px
        $maxWidth = 1400;
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($height * ($maxWidth / $width));
            $canvas = imagecreatetruecolor($newWidth, $newHeight);

            if ($mime === 'image/png') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }

            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $canvas;
        }

        // Save back with high compression (keeping original format to avoid complexity)
        if ($mime === 'image/png') {
            imagepng($image, $path, 8); // High compression level
        } else {
            imagejpeg($image, $path, 75); // Good balance
        }

        imagedestroy($image);
    }
}
