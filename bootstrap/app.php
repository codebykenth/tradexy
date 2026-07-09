<?php

use App\Http\Middleware\EnsureTradingModeSet;
use App\Http\Middleware\TransactionalRequest;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->redirectTo(
            guests: '/login',
            users: '/dashboard'
        );

        $middleware->appendToGroup('web', TransactionalRequest::class);
        $middleware->appendToGroup('web', EnsureTradingModeSet::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackUserActivity::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\TurboRedirect::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Returns real error in dev, friendly message in production
        $devMessage = fn (\Throwable $e, string $fallback): string => app()->isProduction() ? $fallback : $e->getMessage();

        // Helper to check if we are on a mission-critical page where redirects cause loops
        $isEntryPath = function () {
            $path = request()->path();

            return $path === 'dashboard' || $path === 'login' || $path === '/' || empty($path);
        };

        // Shared logic to avoid redirect loops
        $shouldRedirectToDashboard = function (Throwable $e) use ($isEntryPath) {
            if ($isEntryPath()) {
                return false;
            }

            return auth()->check();
        };

        // Authentication (not logged in)
        $exceptions->renderable(function (AuthenticationException $e) use ($devMessage) {
            return redirect()->route('login')
                ->with('error', $devMessage($e, 'Please log in to continue.'));
        });

        // Record not found (findOrFail)
        $exceptions->renderable(function (ModelNotFoundException $e) use ($devMessage, $isEntryPath) {
            if ($isEntryPath()) {
                return null;
            }

            return redirect()->to('/dashboard')
                ->with('error', $devMessage($e, 'The requested record was not found.'));
        });

        // Database errors (constraint violations, etc.)
        $exceptions->renderable(function (QueryException $e) use ($devMessage, $isEntryPath) {
            if ($isEntryPath()) {
                return null;
            }

            return redirect()->back()->withInput()
                ->with('error', $devMessage($e, 'A database error occurred. Please try again.'));
        });

        // Other HTTP exceptions (500, 503, etc.)
        $exceptions->renderable(function (HttpException $e) use ($devMessage, $shouldRedirectToDashboard) {
            if ($e->getStatusCode() >= 500) {
                return null;
            }
            if ($shouldRedirectToDashboard($e)) {
                return redirect()->intended('/dashboard')->with('error', $devMessage($e, 'An error occurred.'));
            }

            return null;
        });

        // Generic catch-all (anything unexpected)
        $exceptions->renderable(function (\Throwable $e) use ($devMessage, $shouldRedirectToDashboard) {
            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'message' => $devMessage($e, 'Something went wrong.'),
                    'status' => 500,
                ], 500);
            }

            if (!app()->isProduction()) {
                return null;
            }

            if ($shouldRedirectToDashboard($e)) {
                return redirect()->to('/dashboard')
                    ->with('error', $devMessage($e, 'Something went wrong. Please try again.'));
            }

            return null;
        });

    })->create();
