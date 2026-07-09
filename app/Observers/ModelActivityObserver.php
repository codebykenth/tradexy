<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class ModelActivityObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logActivity($model, 'created');
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->logActivity($model, 'updated');
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logActivity($model, 'deleted');
    }

    /**
     * Log the activity to the database.
     */
    private function logActivity(Model $model, string $action): void
    {
        $modelName = class_basename($model);

        // Determine user_id: use model's user_id or currently authenticated user
        $userId = $model->user_id ?? Auth::id();

        // Prevent foreign key violation if the User record is already deleted
        $isUserDeletion = $model instanceof \App\Models\User && $action === 'deleted';
        if ($isUserDeletion) {
            $userId = null;
        }

        if (!$userId && !$isUserDeletion) {
            return;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => strtolower($modelName).'_'.$action,
            'description' => "{$modelName} record was {$action}",
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'payload' => json_encode($model->only($model->getFillable())),
        ]);
    }
}
