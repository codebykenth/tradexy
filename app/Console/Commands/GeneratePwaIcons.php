<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class GeneratePwaIcons extends Command
{
    protected $signature = 'pwa:icons';

    protected $description = 'Generate PWA icons from the logo';

    public function handle(): int
    {
        $logoPath = public_path('images/logo.png');

        if (!file_exists($logoPath)) {
            $this->error('Logo not found at: '.$logoPath);

            return self::FAILURE;
        }

        $src = imagecreatefromstring(file_get_contents($logoPath));
        if (!$src) {
            $this->error('Failed to load logo image');

            return self::FAILURE;
        }

        $iconsDir = public_path('icons');
        if (!is_dir($iconsDir)) {
            mkdir($iconsDir, 0755, true);
        }

        $sizes = [72, 96, 128, 144, 152, 192, 384, 512];

        foreach ($sizes as $s) {
            $dst = imagecreatetruecolor($s, $s);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $s, $s, imagesx($src), imagesy($src));
            imagepng($dst, $iconsDir.'/icon-'.$s.'x'.$s.'.png');
            imagedestroy($dst);
            $this->info("Generated icon-{$s}x{$s}.png");
        }

        imagedestroy($src);
        $this->info('All PWA icons generated successfully!');

        return self::SUCCESS;
    }
}
