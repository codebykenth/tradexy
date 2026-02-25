<?php

namespace App\Services;

use App\Models\Trade;
use Exception;
use Illuminate\Support\Facades\Http;

class BybitService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl = 'https://api.bybit.com';
    private string $recvWindow = '50000';

    public function __construct()
    {
        $this->apiKey = config('services.bybit.key');
        $this->apiSecret = config('services.bybit.secret');

        if (!$this->apiKey || !$this->apiSecret) {
            throw new Exception('Bybit API credentials not configured');
        }
    }

    /**
     * Generate authentication headers for Bybit API
     * Equivalent of the Express.js bybitAuth middleware
     *
     * @param string $method - 'GET' or 'POST'
     * @param array $params - Query params (GET) or body (POST)
     * @return array - Headers array for the request
     */
    private function generateAuthHeaders(string $method, array $params = []): array
    {
        // Generate timestamp in milliseconds
        $timestamp = (string) round(microtime(true) * 1000);

        // Build the query string for signing
        if ($method === 'POST') {
            // For POST: timestamp + apiKey + recvWindow + JSON body
            $queryString = $timestamp . $this->apiKey . $this->recvWindow . json_encode($params);
        } else {
            // For GET: timestamp + apiKey + recvWindow + URL params
            $queryString = $timestamp . $this->apiKey . $this->recvWindow . http_build_query($params);
        }

        // Generate HMAC-SHA256 signature
        $signature = hash_hmac('sha256', $queryString, $this->apiSecret);

        return [
            'X-BAPI-API-KEY' => $this->apiKey,
            'X-BAPI-SIGN' => $signature,
            'X-BAPI-TIMESTAMP' => $timestamp,
            'X-BAPI-RECV-WINDOW' => $this->recvWindow,
            'Content-Type' => 'application/json',
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
        $headers = $this->generateAuthHeaders('GET', $params);

        $response = Http::withHeaders($headers)
            ->get($this->baseUrl . $endpoint, $params);

        return $response->json();
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
        $headers = $this->generateAuthHeaders('POST', $body);

        $response = Http::withHeaders($headers)
            ->post($this->baseUrl . $endpoint, $body);

        return $response->json();
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