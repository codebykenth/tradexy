<?php

use App\Database\PostgresConnector;

function connectorDsn(array $config): string
{
    $connector = new PostgresConnector;
    $method = new ReflectionMethod($connector, 'getDsn');

    return $method->invoke($connector, $config);
}

test('it adds the endpoint option for a direct Neon host', function () {
    $dsn = connectorDsn([
        'host' => 'ep-late-dew-a11eo4kk.ap-southeast-1.aws.neon.tech',
        'port' => 5432,
        'database' => 'neondb',
    ]);

    expect($dsn)->toContain('options=endpoint=ep-late-dew-a11eo4kk');
});

test('it strips the pooler suffix from a pooled Neon host', function () {
    $dsn = connectorDsn([
        'host' => 'ep-late-dew-a11eo4kk-pooler.ap-southeast-1.aws.neon.tech',
        'port' => 5432,
        'database' => 'neondb',
    ]);

    expect($dsn)->toContain('options=endpoint=ep-late-dew-a11eo4kk')
        ->not->toContain('options=endpoint=ep-late-dew-a11eo4kk-pooler');
});

test('it honours an explicitly configured Neon endpoint', function () {
    $dsn = connectorDsn([
        'host' => 'db.example.com',
        'port' => 5432,
        'database' => 'app',
        'neon_endpoint' => 'ep-explicit-123',
    ]);

    expect($dsn)->toContain('options=endpoint=ep-explicit-123');
});

test('it leaves non-Neon hosts untouched', function () {
    $dsn = connectorDsn([
        'host' => '127.0.0.1',
        'port' => 5432,
        'database' => 'app',
    ]);

    expect($dsn)->not->toContain('options=endpoint=');
});
