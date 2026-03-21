<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTradingModeSet
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            // Always favor the database preference for authenticated users
            session(['account_mode' => $user->account_mode ?? 'real']);
            session(['market_type' => $user->market_type ?? 'crypto']);
            session(['preferred_currency' => $user->preferred_currency ?? 'USD']);
        } else {
            if (!session()->has('account_mode')) {
                session(['account_mode' => 'real']);
            }

            if (!session()->has('market_type')) {
                session(['market_type' => 'crypto']);
            }

            if (!session()->has('preferred_currency')) {
                session(['preferred_currency' => 'USD']);
            }
        }

        return $next($request);
    }
}
