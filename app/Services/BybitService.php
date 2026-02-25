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
    public function getClosedPnl(int $userId, int $days = 2): array
    {
        $endDate = now();
        $startDate = now()->subDays($days);

        $params = [
            'category' => 'linear',
            'startTime' => (string) ($startDate->getTimestampMs()),
            'endTime' => (string) ($endDate->getTimestampMs()),
        ];

        $response = $this->get('/v5/position/closed-pnl', $params);
        dd($response);

        $trades = [];
        $errors = [];

        if (($response['retCode'] ?? -1) === 0 && isset($response['result']['list'])) {
            $trades = $response['result']['list'];
        } else {
            $errors[] = [
                'period' => "{$startDate->toISOString()} to {$endDate->toISOString()}",
                'error' => $response['retMsg'] ?? 'Unknown error',
            ];
        }

        // Sort by updatedTime ascending (oldest first)
        usort($trades, function ($a, $b) {
            $timeA = (int) ($a['updatedTime'] ?? $a['createdTime'] ?? 0);
            $timeB = (int) ($b['updatedTime'] ?? $b['createdTime'] ?? 0);
            return $timeA - $timeB;
        });

        // Save trades to database
        $created = 0;
        $skipped = 0;

        foreach ($trades as $trade) {
            // Bybit's "side" is the CLOSING side, so entry is the opposite
            $closeSide = strtolower($trade['side']) === 'buy' ? 'long' : 'short';
            $entrySide = $closeSide === 'long' ? 'short' : 'long';

            // Convert millisecond timestamps to datetime
            $openDatetime = \Carbon\Carbon::createFromTimestampMs((int) $trade['createdTime']);
            $closeDatetime = \Carbon\Carbon::createFromTimestampMs((int) $trade['updatedTime']);

            // Use firstOrCreate to prevent duplicate trades
            $result = Trade::firstOrCreate(
                [
                    'user_id' => $userId,
                    'order_id' => $trade['orderId'],
                ],
                [
                    'symbol' => $trade['symbol'],
                    'entry_side' => $entrySide,
                    'exit_side' => $closeSide,
                    'entry_price' => $trade['avgEntryPrice'],
                    'exit_price' => $trade['avgExitPrice'],
                    'quantity' => $trade['closedSize'],
                    'cum_entry_value' => $trade['cumEntryValue'],
                    'cum_exit_value' => $trade['cumExitValue'],
                    'avg_entry_price' => $trade['avgEntryPrice'],
                    'avg_exit_price' => $trade['avgExitPrice'],
                    'leverage' => $trade['leverage'],
                    'open_fees' => $trade['openFee'] ?? 0,
                    'close_fees' => $trade['closeFee'] ?? 0,
                    'closed_pnl' => $trade['closedPnl'],
                    'total_pnl' => $trade['closedPnl'],
                    'open_datetime' => $openDatetime,
                    'close_datetime' => $closeDatetime,
                ]
            );

            $result->wasRecentlyCreated ? $created++ : $skipped++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'summary' => [
                'totalFromApi' => count($trades),
                'created' => $created,
                'skipped' => $skipped,
                'startDate' => $startDate->toISOString(),
                'endDate' => $endDate->toISOString(),
            ],
        ];
    }

    public function getAccountBalance() {
        try {
            $response = $this->get('/v5/account/wallet-balance', [
                'coin' => 'USDT',
                'accountType' => 'UNIFIED',

            ]);
            dd($response);
        } catch (Exception $e) {
            return [
                'error' => $e
            ];
        }
    }
}