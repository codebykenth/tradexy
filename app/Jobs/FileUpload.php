<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\FileService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class FileUpload implements ShouldQueue
{
    use Queueable;

    private ?string $fileUrl = null;

    /**
     * @param  string  $tempPath  Local path to the temporary file
     * @param  string  $directory  Remote directory in Firebase
     * @param  string  $modelClass  The model to update (e.g. Trade::class)
     * @param  string  $modelId  The ID of the record
     * @param  string  $field  The DB field to fill (e.g. 'chart_picture')
     * @param  string|null  $oldFileUrl  Optional URL of the file to replace
     */
    public function __construct(
        private readonly string $tempPath,
        private readonly string $directory,
        private readonly string $modelClass,
        private readonly string $modelId,
        private readonly string $field,
        private readonly ?string $oldFileUrl = null
    ) {}

    public function handle(FileService $fileService): void
    {
        // 1. Get the temporary file from local storage
        $fullPath = Storage::disk('local')->path($this->tempPath);

        if (!file_exists($fullPath)) {
            throw new Exception("Temporary file not found at: {$fullPath}");
        }

        $uploadedFile = new UploadedFile(
            path: $fullPath,
            originalName: basename($fullPath),
            error: UPLOAD_ERR_OK,
            test: true
        );

        // 2. Upload to Firebase (and delete old one if exists)
        $this->fileUrl = $fileService->updateFile(
            $this->oldFileUrl,
            $uploadedFile,
            $this->directory,
            null
        );

        // 3. Update the Model
        $model = $this->modelClass::find($this->modelId);

        if ($model && $this->fileUrl) {
            $model->update([
                $this->field => $this->fileUrl,
            ]);
        }

        // 4. Cleanup local temp file
        Storage::disk('local')->delete($this->tempPath);
    }

    public function failed(\Throwable $exception): void
    {
        $fileService = app(FileService::class);

        // If upload happened but something else failed, cleanup Firebase
        if ($this->fileUrl) {
            $fileService->deleteFile(
                $this->fileUrl,
                $this->directory,
                null
            );
        }

        // Always cleanup local temp file
        Storage::disk('local')->delete($this->tempPath);
    }
}
