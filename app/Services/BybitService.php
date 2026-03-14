<?php

namespace App\Services;

use App\Models\Trade;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Added for logging

class BybitService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;
    private string $recvWindow = '20000'; // 20s is safer than 50s for some gateway checks

    public function __construct(bool $isDemo = false)
    {
        if ($isDemo) {
            $this->apiKey = trim((string) config('services.bybit.demo_key'));
            $this->apiSecret = trim((string) config('services.bybit.demo_secret'));
            $this->baseUrl = rtrim((string) config('services.bybit.demo_base_url', 'https://api-testnet.bybit.com'), '/');
        } else {
            $this->apiKey = trim((string) config('services.bybit.key'));
            $this->apiSecret = trim((string) config('services.bybit.secret'));
            $this->baseUrl = rtrim((string) config('services.bybit.base_url', 'https://api.bybit.com'), '/');
        }

        if (!$this->apiKey || !$this->apiSecret) {
            throw new Exception($isDemo ? 'Bybit Demo API credentials not configured' : 'Bybit API credentials not configured');
        }
    }

    /**
     * Generate authentication headers for Bybit API V5
     *
     * @param string $payload - The exact stringified query/body being sent
     * @return array - Headers array for the request
     */
    private function generateAuthHeaders(string $payload): array
    {
        // Use Carbon for precise millisecond timestamp
        $timestamp = (string) now()->getTimestampMs();

        // Signature for V5: timestamp + api_key + recv_window + payload
        $signatureString = $timestamp . $this->apiKey . $this->recvWindow . $payload;
        $signature = hash_hmac('sha256', $signatureString, $this->apiSecret);

        return [
            'X-BAPI-API-KEY' => $this->apiKey,
            'X-BAPI-SIGN' => $signature,
            'X-BAPI-TIMESTAMP' => $timestamp,
            'X-BAPI-RECV-WINDOW' => $this->recvWindow,
            'Content-Type' => 'application/json',
            'User-Agent' => 'TradingJournal/2.0',
        ];
    }

    /**
     * Make an authenticated GET request
     *
     * @param string $endpoint - API endpoint (e.g. '/v5/position/closed-pnl')
     * @param array $params - Query parameters
     * @return array - API response
     */
    public function get(string $endpoint, array $params = []): array
    {
        ksort($params);
        
        // Build query string — Bybit v5 expects typical GET query string
        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        
        $headers = $this->generateAuthHeaders($queryString);

        $url = $this->baseUrl . $endpoint . ($queryString ? '?' . $queryString : '');

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->get($url);

        if ($response->failed()) {
            $body = $response->body();
            // Log 401 details for debugging (avoid logging the actual secret)
            if ($response->status() === 401) {
                Log::error("Bybit 401 Error. URL: {$url}. Headers (redacted): " . json_encode(array_merge($headers, ['X-BAPI-SIGN' => 'HIDDEN'])));
            }
            throw new Exception("Bybit API Error (GET {$endpoint}): Status {$response->status()} - Body: {$body}");
        }

        return $response->json();
    }

    /**
     * Make an authenticated POST request
     *
     * @param string $endpoint - API endpoint
     * @param array $body - Request body
     * @return array - API response
     */
    public function post(string $endpoint, array $body = []): array
    {
        $jsonBody = empty($body) ? '' : json_encode($body, JSON_UNESCAPED_SLASHES);
        
        $headers = $this->generateAuthHeaders($jsonBody);

        $response = Http::withHeaders($headers)
            ->withBody($jsonBody, 'application/json')
            ->timeout(30)
            ->post($this->baseUrl . $endpoint);

        if ($response->failed()) {
            throw new Exception("Bybit API Error (POST {$endpoint}): Status {$response->status()} - Body: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Fetch closed PnL
     *
     * @param int $days - Number of days to look back (default: 2)
     * @return array - ['trades' => [...], 'errors' => [...], 'summary' => [...]]
     */
    public function getClosedPnl(int $days = 2): array
    {
        $params = [
            'category' => 'linear',
            'startTime' => (string) now()->subDays($days)->getTimestampMs(),
            'endTime' => (string) now()->getTimestampMs(),
            'limit' => '100',
        ];

        return $this->get('/v5/position/closed-pnl', $params);
    }

    /**
     * Fetch account balance
     *
     * @return array - API response containing account balance information
     */
    public function getAccountBalance(): array
    {
        return $this->get('/v5/account/wallet-balance', [
            'coin' => 'USDT',
            'accountType' => 'UNIFIED',
        ]);
    }
}