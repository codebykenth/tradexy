<?php

namespace App\Services;

use App\Models\Trade;
use Exception;
use Illuminate\Support\Facades\Http;

class BybitService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;
    private string $recvWindow = '50000';

    public function __construct()
    {
        $this->apiKey = trim((string) config('services.bybit.key'));
        $this->apiSecret = trim((string) config('services.bybit.secret'));
        $this->baseUrl = config('services.bybit.base_url', 'https://api.bybit.com');

        if (!$this->apiKey || !$this->apiSecret) {
            throw new Exception('Bybit API credentials not configured');
        }
    }

    /**
     * Generate authentication headers for Bybit API
     *
     * @param string $payload - The exact stringified query/body being sent
     * @return array - Headers array for the request
     */
    private function generateAuthHeaders(string $payload): array
    {
        // Generate timestamp in milliseconds (safer float-to-int cast)
        $timestamp = (string) (int) (microtime(true) * 1000);

        // The query string to sign MUST precisely match the payload sent
        $queryString = $timestamp . $this->apiKey . $this->recvWindow . $payload;

        // Generate HMAC-SHA256 signature
        $signature = hash_hmac('sha256', $queryString, $this->apiSecret);

        return [
            'X-BAPI-API-KEY' => $this->apiKey,
            'X-BAPI-SIGN' => $signature,
            'X-BAPI-TIMESTAMP' => $timestamp,
            'X-BAPI-RECV-WINDOW' => $this->recvWindow,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'TradingJournal/1.0',
        ];
    }

    /**
     * Make an authenticated GET request to Bybit API
     *
     * @param string $endpoint - API endpoint (e.g. '/v5/position/closed-pnl')
     * @param array $params - Query parameters
     * @return array - API response
     */
    public function get(string $endpoint, array $params = []): array
    {
        ksort($params); // Always sort params before signature for Bybit GET
        // Build exact query string
        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        
        // Pass exact string to signature generator
        $headers = $this->generateAuthHeaders($queryString);

        // Append query string manually to ensure Guzzle doesn't rebuild it differently
        $url = $this->baseUrl . $endpoint;
        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }

        $response = Http::withHeaders($headers)
            ->get($url);

        $data = $response->json();

        if ($data === null) {
            throw new Exception("Bybit API Error (GET {$endpoint}): Status {$response->status()} - Body: " . $response->body());
        }

        return $data;
    }

    /**
     * Make an authenticated POST request to Bybit API
     *
     * @param string $endpoint - API endpoint
     * @param array $body - Request body
     * @return array - API response
     */
    public function post(string $endpoint, array $body = []): array
    {
        // Encode JSON explicitly with unescaped slashes to match how Bybit expects it
        $jsonBody = empty($body) ? '' : json_encode($body, JSON_UNESCAPED_SLASHES);
        
        $headers = $this->generateAuthHeaders($jsonBody);

        $response = Http::withHeaders($headers)
            ->withBody($jsonBody, 'application/json')
            ->post($this->baseUrl . $endpoint);

        $data = $response->json();

        if ($data === null) {
            throw new Exception("Bybit API Error (POST {$endpoint}): Status {$response->status()} - Body: " . $response->body());
        }

        return $data;
    }

    /**
     * Fetch closed PnL from Bybit API for the last N days
     *
     * @param int $days - Number of days to look back (default: 2)
     * @return array - ['trades' => [...], 'errors' => [...], 'summary' => [...]]
     */
    public function getClosedPnl(int $days = 2): array
    {
        $endDate = now();
        $startDate = now()->subDays($days);

        $params = [
            'category' => 'linear',
            'startTime' => (string) ($startDate->getTimestampMs()),
            'endTime' => (string) ($endDate->getTimestampMs()),
        ];

        $response = $this->get('/v5/position/closed-pnl', $params);

        return $response;
    }

    public function getAccountBalance()
    {
        try {
            $response = $this->get('/v5/account/wallet-balance', [
                'coin' => 'USDT',
                'accountType' => 'UNIFIED',

            ]);
            return $response;
        } catch (Exception $e) {
            return [
                'error' => $e
            ];
        }
    }
}