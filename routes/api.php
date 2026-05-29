<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

$authorizeCron = function (Request $request): ?JsonResponse {
    $providedToken = (string) ($request->query('token') ?? $request->header('X-Cron-Token') ?? '');
    $expectedToken = (string) config('app.cron_token', '');

    if ($expectedToken === '' || !hash_equals($expectedToken, $providedToken)) {
        return response()->json([
            'ok' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    return null;
};

$runCommand = function (Request $request, string $command) use ($authorizeCron) {
    $authResponse = $authorizeCron($request);
    if ($authResponse !== null) {
        return $authResponse;
    }

    $async = filter_var((string) $request->query('async', '0'), FILTER_VALIDATE_BOOL);
    if ($async) {
        app()->terminating(function () use ($command): void {
            try {
                Artisan::call($command);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        return response()->json([
            'ok' => true,
            'accepted' => true,
            'command' => $command,
            'mode' => 'async',
            'message' => 'Command accepted and will continue after response.',
            'ran_at' => now()->toIso8601String(),
        ], 202);
    }

    $exitCode = Artisan::call($command);

    return response()->json([
        'ok' => $exitCode === 0,
        'command' => $command,
        'mode' => 'sync',
        'exit_code' => $exitCode,
        'output' => trim(Artisan::output()),
        'ran_at' => now()->toIso8601String(),
    ]);
};

// Route::match(['GET', 'POST'], '/cron/schedule-run', fn (Request $request) => $runCommand($request, 'schedule:run'))
//     ->middleware('throttle:120,1');

// Direct command routes based on routes/console.php schedules
Route::match(['GET', 'POST'], '/cron/trades-fetch-pnl', fn (Request $request) => $runCommand($request, 'trades:fetch-pnl'))
    ->middleware('throttle:120,1');
Route::match(['GET', 'POST'], '/cron/trades-fetch-pnl-demo', fn (Request $request) => $runCommand($request, 'trades:fetch-pnl --demo'))
    ->middleware('throttle:120,1');
Route::match(['GET', 'POST'], '/cron/account-fetch-balance', fn (Request $request) => $runCommand($request, 'account:fetch-balance'))
    ->middleware('throttle:120,1');
Route::match(['GET', 'POST'], '/cron/account-fetch-balance-demo', fn (Request $request) => $runCommand($request, 'account:fetch-balance --demo'))
    ->middleware('throttle:120,1');
Route::match(['GET', 'POST'], '/cron/generate-daily-news', fn (Request $request) => $runCommand($request->merge(['async' => $request->query('async', '1')]), 'generate:daily-news'))
    ->middleware('throttle:120,1');
Route::match(['GET', 'POST'], '/cron/logs-cleanup', fn (Request $request) => $runCommand($request, 'logs:cleanup'))
    ->middleware('throttle:120,1');
Route::match(['GET', 'POST'], '/cron/db-backup', fn (Request $request) => $runCommand($request, 'db:backup'))
    ->middleware('throttle:120,1');
