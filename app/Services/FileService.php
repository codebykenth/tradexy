<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Service for handling file operations in Google Cloud Storage
 * Provides methods for uploading, updating, and deleting files
 */
class FileService
{
    /**
     * Get the base directory based on environment (local, production, etc.)
     */
    private function getEnvPrefix(): string
    {
        $env = config('app.env', 'production');

        return ($env === 'production' || $env === 'prod') ? '' : 'test/';
    }

    /**
     * Upload a file to Google Cloud Storage
     *
     * @param  \Illuminate\Http\UploadedFile|null  $file  The file to upload
     * @param  string  $folderPath  The path within the bucket to store the file
     * @param  string|null  $referenceId  Optional reference ID (e.g., product ID) to organize files
     * @return string|null URL to the uploaded file, or null if upload failed
     */
    public function uploadFile($file, string $folderPath, ?string $referenceId = null): ?string
    {
        if (!$file) {
            return null;
        }

        // Create unique filename and force .webp extension
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = preg_replace('/[^A-Za-z0-9_-]/', '_', $baseName);
        $fileName = time().'_'.$cleanName.'.webp';

        // Build path correctly with environment prefix
        $envPrefix = $this->getEnvPrefix();
        $basePath = ltrim("$envPrefix$folderPath", '/');
        $path = $referenceId ? "$basePath/$referenceId" : $basePath;

        // Store the file in Google Cloud Storage
        $this->compressImage($file);

        $storeFile = $file->storeAs($path, $fileName, 'gcs');

        // Make the file publicly accessible
        try {
            Storage::disk('gcs')->setVisibility($storeFile, 'public');
        } catch (\Exception $e) {
            // Ignore visibility error if Uniform Bucket-Level Access is on
        }

        // Get the public URL for the file
        return Storage::disk('gcs')->url($storeFile);
    }

    /**
     * Update an existing file with a new one
     */
    public function updateFile($url, $file, $folderPath, $referenceId = null)
    {
        $fileUrl = $url;

        if ($file) {
            // Delete the old file if it exists
            if ($url) {
                $this->deleteFile($url, $folderPath, $referenceId);
            }

            // Upload the new file
            $fileUrl = $this->uploadFile($file, $folderPath, $referenceId);
        }

        return $fileUrl;
    }

    /**
     * Delete a file from Google Cloud Storage
     */
    public function deleteFile(?string $url, string $folderPath, ?string $referenceId = null)
    {
        // Skip if URL is empty
        if (!$url || !str_starts_with($url, 'http')) {
            return false;
        }

        try {
            // Robust Path Extraction: Extract the relative path from the URL
            $path = $this->getRelativePathFromUrl($url);

            // Check if the file exists before attempting deletion
            if (Storage::disk('gcs')->exists($path)) {
                return Storage::disk('gcs')->delete($path);
            }
        } catch (\Exception $e) {
            report($e);
        }

        return false;
    }

    /**
     * Compress, resize, and convert an image to WebP before uploading
     */
    private function compressImage($file): void
    {
        $path = $file->getRealPath();
        $mime = $file->getMimeType();

        // Load the image based on its type
        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => null,
        };

        if (!$image) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // 1. Resize if wider than 1400px (Desktop resolution limit for charts)
        if ($width > 1400) {
            $newWidth = 1400;
            $newHeight = (int) ($height * (1400 / $width));
            $tmp = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency for PNG/WebP
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
                imagefilledrectangle($tmp, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $tmp;
        }

        // 2. Convert to WebP (Always) with 80% quality
        imagewebp($image, $path, 80);
        imagedestroy($image);
    }

    /**
     * Correctly extract the relative GCS path from a full URL.
     * Collapses duplicated segments and strips bucket/domain.
     */
    private function getRelativePathFromUrl(string $url): string
    {
        // 1. First, collapse any duplicated path segments (e.g. /bucket/bucket/)
        $url = preg_replace('/\/([^\/]+)\/\1\//', '/$1/', $url);

        $bucket = config('filesystems.disks.gcs.bucket');
        $path = parse_url($url, PHP_URL_PATH);

        // 2. Strip the leading /bucket-name/ from the path if it exists
        if ($bucket && str_starts_with((string) $path, "/{$bucket}/")) {
            return substr((string) $path, strlen("/{$bucket}/"));
        }

        // 3. Fallback: just strip leading slashes
        return ltrim((string) $path, '/');
    }

    /**
     * Extract a filename from a full URL
     */
    public function getFileNameFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        return basename(urldecode((string) $path));
    }
}
