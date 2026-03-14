<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class TrackUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $now = now();
            $lastSeen = $user->last_seen_at;

            // Definition of a "continuous session" gap (e.g., 15 minutes)
            $sessionTimeout = 15;

            // Throttle: Only update the database once every 60 seconds
            if (!$lastSeen || $now->diffInSeconds($lastSeen) >= 60) {
                if ($lastSeen && $lastSeen->diffInMinutes($now) < $sessionTimeout) {
                    // User is in a continuous session, add the gap to total duration
                    $durationGap = $now->getTimestamp() - $lastSeen->getTimestamp();
                    if ($durationGap > 0) {
                        $user->increment('total_duration', $durationGap);
                    }
                }

                // Update last_seen_at to keep the tracking point fresh
                // We update quietly to avoid triggering "updated" observers on every request
                $user->updateQuietly(['last_seen_at' => $now]);
            }
        }

        return $next($request);
    }
}
