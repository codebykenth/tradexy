<?php

declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector as BasePostgresConnector;

/**
 * Adds Neon's endpoint fallback to the PostgreSQL DSN.
 *
 * Neon routes direct connections using TLS SNI, which requires libpq >= 14.
 * Some serverless PHP builds (for example the Vercel PHP 8.5 runtime, which
 * bundles AlmaLinux's older libpq) do not support SNI. Neon accepts the
 * endpoint ID explicitly in that case, so we append it to the DSN.
 *
 * @see https://neon.com/sni
 */
final class PostgresConnector extends BasePostgresConnector
{
    /**
     * Create a DSN string from a configuration.
     */
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        $endpoint = $config['neon_endpoint'] ?? $this->endpointFromHost($config['host'] ?? null);

        if (is_string($endpoint) && $endpoint !== '') {
            $dsn .= ";options=endpoint={$endpoint}";
        }

        return $dsn;
    }

    /**
     * Derive the Neon endpoint ID from a direct or pooled Neon hostname.
     */
    private function endpointFromHost(mixed $host): ?string
    {
        if (!is_string($host) || !str_ends_with($host, '.neon.tech')) {
            return null;
        }

        $endpoint = preg_replace('/-pooler$/', '', explode('.', $host, 2)[0]);

        return str_starts_with($endpoint, 'ep-') ? $endpoint : null;
    }
}
