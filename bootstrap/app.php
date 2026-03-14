<?php

use App\Http\Middleware\TransactionalRequest;
use App\Http\Middleware\EnsureTradingModeSet;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->appendToGroup('web', TransactionalRequest::class);
        $middleware->appendToGroup('web', EnsureTradingModeSet::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackUserActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Returns real error in dev, friendly message in production
        $devMessage = fn(Throwable $e, string $fallback): string =>
            app()->isProduction() ? $fallback : $e->getMessage();

        // Validation errors — let Laravel handle normally
        $exceptions->renderable(function (ValidationException $e) {
            return null;
        });

        // Authentication (not logged in)
        $exceptions->renderable(function (AuthenticationException $e) use ($devMessage) {
            return redirect()->route('login')
                ->with('error', $devMessage($e, 'Please log in to continue.'));
        });

        // Authorization (forbidden / policy denied)
        $exceptions->renderable(function (AuthorizationException $e) use ($devMessage) {
            return redirect()->back()
                ->with('error', $devMessage($e, 'You are not authorized to perform this action.'));
        });

        // Record not found (findOrFail)
        $exceptions->renderable(function (ModelNotFoundException $e) use ($devMessage) {
            return redirect()->to('/dashboard')
                ->with('error', $devMessage($e, 'The requested record was not found.'));
        });

        // CSRF token mismatch (expired session)
        $exceptions->renderable(function (TokenMismatchException $e) use ($devMessage) {
            return redirect()->back()->withInput()
                ->with('error', $devMessage($e, 'Your session has expired. Please try again.'));
        });

        // Rate limiting (too many requests)
        $exceptions->renderable(function (ThrottleRequestsException $e) use ($devMessage) {
            return redirect()->back()
                ->with('error', $devMessage($e, 'Too many requests. Please wait a moment and try again.'));
        });

        // Upload too large
        $exceptions->renderable(function (PostTooLargeException $e) use ($devMessage) {
            return redirect()->back()
                ->with('error', $devMessage($e, 'The uploaded file is too large.'));
        });

        // Method not allowed (e.g. GET on a POST-only route)
        $exceptions->renderable(function (MethodNotAllowedHttpException $e) use ($devMessage) {
            return redirect()->back()
                ->with('error', $devMessage($e, 'This action is not allowed.'));
        });

        // 404 — route or resource not found - let Laravel show the 404 view
        $exceptions->renderable(function (NotFoundHttpException $e) {
            return null;
        });

        // Database errors (constraint violations, etc.)
        $exceptions->renderable(function (QueryException $e) use ($devMessage) {
            return redirect()->back()->withInput()
                ->with('error', $devMessage($e, 'A database error occurred. Please try again.'));
        });

        // Other HTTP exceptions (500, 503, etc.)
        $exceptions->renderable(function (HttpException $e) use ($devMessage) {
            $fallbackMsg = match ($e->getStatusCode()) {
                403 => 'Access denied.',
                500 => 'An internal server error occurred.',
                503 => 'Service is temporarily unavailable. Please try again later.',
                default => 'An error occurred. Please try again.',
            };

            // If it's a 500 or similar, don't redirect back to avoid loops if the page itself is broken
            if ($e->getStatusCode() >= 500) {
                return null; 
            }

            return redirect()->intended('/dashboard')->with('error', $devMessage($e, $fallbackMsg));
        });

        // Generic catch-all (anything unexpected)
        $exceptions->renderable(function (Throwable $e) use ($devMessage) {
            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }

            if (request()->expectsJson()) {
                return null;
            }

            // In local/dev, show the full error page (don't redirect) to help debugging
            if (!app()->isProduction()) {
                return null;
            }

            // Avoid redirect loops if the dashboard or login page is broken
            $currentPath = request()->path();
            if ($currentPath === 'dashboard' || $currentPath === 'login' || $currentPath === '/') {
                return null; 
            }

            return redirect()->to('/dashboard')
                ->with('error', $devMessage($e, 'Something went wrong. Please try again.'));
        });

    })->create();

