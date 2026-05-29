<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Throwable;

trait DispatchesQueueOrSync
{
    /**
     * Dispatch a job to queue, or run sync when configured for serverless.
     */
    protected function dispatchJob(object $job): void
    {
        $shouldRunSync = (bool) config('queue.force_sync', false)
            || config('queue.default') === 'sync';

        try {
            if ($shouldRunSync) {
                dispatch_sync($job);
            } else {
                dispatch($job);
            }
        } catch (Throwable $e) {
            // Safe fallback for platforms without long-running workers.
            report($e);
            dispatch_sync($job);
        }
    }
}
