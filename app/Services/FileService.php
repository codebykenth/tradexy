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
    public function uploadFile($file, string $folderPath, ?string $referenceId = null, ?string $originalName = null): ?string
    {
        if (!$file) {
            return null;
        }

        // Use original filename (sanitized) to retain user context
        $baseName = $originalName ?? $file->getClientOriginalName();
        $fileName = time().'_'.preg_replace('/[^A-Za-z0-9\._-]/', '_', $baseName);

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
        $url = Storage::disk('gcs')->url($storeFile);

        // Collapse any duplicated path segments (e.g. /bucket/bucket/)
        while (preg_match('/\/([^\/]+)\/\1\//', (string) $url)) {
            $url = preg_replace('/\/([^\/]+)\/\1\//', '/$1/', (string) $url);
        }

        return $url;
    }

    /**
     * Update an existing file with a new one
     */
    public function updateFile($url, $file, $folderPath, $referenceId = null, ?string $originalName = null)
    {
        $fileUrl = $url;

        if ($file) {
            // Delete the old file if it exists
            if ($url) {
                $this->deleteFile($url);
            }

            // Upload the new file
            $fileUrl = $this->uploadFile($file, $folderPath, $referenceId, $originalName);
        }

        return $fileUrl;
    }

    /**
     * Delete a file from Google Cloud Storage
     */
    public function deleteFile(?string $url)
    {
        // Skip if URL is empty
        if (!$url || !str_starts_with($url, 'http')) {
            return false;
        }

        try {
            // Extract the relative path from the URL
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
     * Compress an image file using GD before uploading.
     * Skips when GD is unavailable (e.g. many serverless PHP runtimes).
     */
    private function compressImage($file): void
    {
        if (!\extension_loaded('gd')) {
            return;
        }

        $path = $file->getRealPath();
        $mime = $file->getMimeType();

        // Skip non-image or potentially huge files
        if ($file->getSize() < 200 * 1024) {
            return;
        } // Ignore if < 200KB

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            if (!\function_exists('imagecreatefromjpeg')) {
                return;
            }
            $image = @\imagecreatefromjpeg($path);
            if ($image) {
                \imagejpeg($image, $path, 75); // 75% quality
                \imagedestroy($image);
            }
        } elseif ($mime === 'image/png') {
            if (!\function_exists('imagecreatefrompng')) {
                return;
            }
            $image = @\imagecreatefrompng($path);
            if ($image) {
                \imagealphablending($image, false);
                \imagesavealpha($image, true);
                \imagepng($image, $path, 6); // Level 6 compression
                \imagedestroy($image);
            }
        }
    }

    /**
     * Correctly extract the relative GCS path from a full URL.
     * Collapses duplicated segments and strips bucket/domain.
     */
    private function getRelativePathFromUrl(string $url): string
    {
        // 1. First, collapse any duplicated path segments (e.g. /bucket/bucket/)
        while (preg_match('/\/([^\/]+)\/\1\//', (string) $url)) {
            $url = preg_replace('/\/([^\/]+)\/\1\//', '/$1/', (string) $url);
        }

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
