<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Automatically wraps write requests (POST, PUT, PATCH, DELETE)
 * in a database transaction.
 *
 * - Success → auto COMMIT
 * - Exception → auto ROLLBACK (exception re-thrown for global handler)
 */
final class TransactionalRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        // GET/HEAD/OPTIONS are read-only — no transaction needed
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        // Wrap write requests in a transaction
        return DB::transaction(fn (): Response => $next($request));
    }
}
