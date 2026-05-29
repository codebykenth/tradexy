<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hotwire Turbo requires form submissions to redirect with a 303 "See Other"
 * status code, otherwise the browser repeats the original request against the
 * redirect target. Laravel defaults to 302 for `redirect()` calls, so we bump
 * non-GET redirects to 303 for Turbo-driven requests.
 */
class TurboRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $isTurboRequest = str_contains((string) $request->header('Accept'), 'text/vnd.turbo-stream.html')
            || $request->header('Turbo-Frame')
            || $request->header('X-Turbo');

        if (
            $response->isRedirection()
            && $response->getStatusCode() === 302
            && !in_array($request->getMethod(), ['GET', 'HEAD'], true)
        ) {
            // Always bump POST/PUT/PATCH/DELETE redirects so Turbo follows them
            // with a fresh GET, matching the PRG pattern it expects.
            $response->setStatusCode(303);
        }

        return $response;
    }
}
