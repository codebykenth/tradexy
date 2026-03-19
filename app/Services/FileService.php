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

        // Create unique filename using timestamp
        $fileName = time().'_'.preg_replace('/[^A-Za-z0-9\._-]/', '_', $file->getClientOriginalName());

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
            // Extract the filename from the URL
            $fileName = $this->getFileNameFromUrl($url);

            // Construct the full storage path with environment prefix
            $envPrefix = $this->getEnvPrefix();
            $basePath = ltrim("$envPrefix$folderPath", '/');
            $path = $referenceId
                ? "$basePath/$referenceId/$fileName"
                : "$basePath/$fileName";

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
     * Compress an image file using GD before uploading
     */
    private function compressImage($file): void
    {
        $path = $file->getRealPath();
        $mime = $file->getMimeType();

        // Skip non-image or potentially huge files
        if ($file->getSize() < 200 * 1024) {
            return;
        } // Ignore if < 200KB

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $image = @imagecreatefromjpeg($path);
            if ($image) {
                imagejpeg($image, $path, 75); // 75% quality
                imagedestroy($image);
            }
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($path);
            if ($image) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagepng($image, $path, 6); // Level 6 compression
                imagedestroy($image);
            }
        }
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
