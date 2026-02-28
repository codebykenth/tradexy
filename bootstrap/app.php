<?php

use App\Http\Middleware\TransactionalRequest;
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
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->appendToGroup('web', TransactionalRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Validation errors 
        $exceptions->renderable(function (ValidationException $e) {
            return null;
        });

        // Authentication (not logged in) 
        $exceptions->renderable(function (AuthenticationException $e) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        });

        // Authorization (forbidden / policy denied) 
        $exceptions->renderable(function (AuthorizationException $e) {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        });

        // Record not found (findOrFail) 
        $exceptions->renderable(function (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'The requested record was not found.');
        });

        // CSRF token mismatch (expired session)
        $exceptions->renderable(function (TokenMismatchException $e) {
            return redirect()->back()->withInput()->with('error', 'Your session has expired. Please try again.');
        });

        // Rate limiting (too many requests) 
        $exceptions->renderable(function (ThrottleRequestsException $e) {
            return redirect()->back()->with('error', 'Too many requests. Please wait a moment and try again.');
        });

        // Upload too large 
        $exceptions->renderable(function (PostTooLargeException $e) {
            return redirect()->back()->with('error', 'The uploaded file is too large.');
        });

        // Method not allowed (e.g. GET on a POST-only route) 
        $exceptions->renderable(function (MethodNotAllowedHttpException $e) {
            return redirect()->back()->with('error', 'This action is not allowed.');
        });

        // 404 — route or resource not found 
        $exceptions->renderable(function (NotFoundHttpException $e) {
            return redirect()->back()->with('error', 'Page not found.');
        });

        // Database errors (constraint violations, etc.) 
        $exceptions->renderable(function (QueryException $e) {
            return redirect()->back()->withInput()->with('error', 'A database error occurred. Please try again.');
        });

        // Other HTTP exceptions (500, 503, etc.) 
        $exceptions->renderable(function (HttpException $e) {
            $message = match ($e->getStatusCode()) {
                403 => 'Access denied.',
                500 => 'An internal server error occurred.',
                503 => 'Service is temporarily unavailable. Please try again later.',
                default => 'An error occurred. Please try again.',
            };

            return redirect()->back()->with('error', $message);
        });

        // Generic catch-all (anything unexpected) 
        $exceptions->renderable(function (Throwable $e) {
            // Only handle web requests — let API exceptions return JSON
            if (request()->expectsJson()) {
                return null;
            }

            return redirect()->back()->withInput()->with('error', 'Something went wrong. Please try again.');
        });

    })->create();
