<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class ScreenerService
{
    private const BYBIT_BASE_URL = 'https://api.bybit.com';

    private const KLINE_CACHE_TTL_MINUTES = 15;

    private const TICKER_CACHE_TTL_MINUTES = 5;

    private const KLINE_LIMIT = 500;

    private const HTTP_POOL_BATCH_SIZE = 10;

    // Maps interval labels to Bybit API interval values
    private const INTERVAL_MAP = [
        '1m' => '1',
        '3m' => '3',
        '5m' => '5',
        '15m' => '15',
        '30m' => '30',
        '1h' => '60',
        '1hr' => '60',
        '2h' => '120',
        '4h' => '240',
        '4hr' => '240',
        '6h' => '360',
        '12h' => '720',
        '1d' => 'D',
        '1D' => 'D',
        '1w' => 'W',
        '1W' => 'W',
        '1M' => 'M',
    ];

    // Available indicators and their default periods
    private const INDICATOR_DEFAULTS = [
        'rsi' => ['period' => 14],
        'sma' => ['period' => 20],
        'ema' => ['period' => 12],
        'macd' => ['fast' => 12, 'slow' => 26, 'signal' => 9],
        'bb' => ['period' => 20, 'stddev' => 2],
        'volume_sma' => ['period' => 20],
    ];

    // Fetches all USDT linear perpetual tickers from Bybit
    public function getMarketTickers(): array
    {
        return Cache::remember('screener_tickers', now()->addMinutes(self::TICKER_CACHE_TTL_MINUTES), function () {
            $response = Http::timeout(15)->get(self::BYBIT_BASE_URL.'/v5/market/tickers', [
                'category' => 'linear',
            ]);

            if ($response->failed()) {
                Log::error('Screener: Failed to fetch tickers', ['status' => $response->status()]);

                return [];
            }

            $data = $response->json();
            if (($data['retCode'] ?? -1) !== 0) {
                Log::error('Screener: Bybit API error', ['retMsg' => $data['retMsg'] ?? 'Unknown']);

                return [];
            }

            $tickers = [];
            foreach ($data['result']['list'] ?? [] as $item) {
                // Only include USDT pairs
                if (!str_ends_with($item['symbol'], 'USDT')) {
                    continue;
                }

                $tickers[$item['symbol']] = [
                    'symbol' => $item['symbol'],
                    'lastPrice' => (float) ($item['lastPrice'] ?? 0),
                    'prevPrice24h' => (float) ($item['prevPrice24h'] ?? 0),
                    'price24hPcnt' => round((float) ($item['price24hPcnt'] ?? 0) * 100, 2),
                    'volume24h' => (float) ($item['volume24h'] ?? 0),
                    'turnover24h' => (float) ($item['turnover24h'] ?? 0),
                    'highPrice24h' => (float) ($item['highPrice24h'] ?? 0),
                    'lowPrice24h' => (float) ($item['lowPrice24h'] ?? 0),
                    'bid1Price' => (float) ($item['bid1Price'] ?? 0),
                    'ask1Price' => (float) ($item['ask1Price'] ?? 0),
                ];
            }

            return $tickers;
        });
    }

    // Fetches OHLCV kline data for a symbol with caching
    public function getKlineData(string $symbol, string $interval = 'D', int $limit = self::KLINE_LIMIT): array
    {
        $cacheKey = "screener_kline_{$symbol}_{$interval}_{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(self::KLINE_CACHE_TTL_MINUTES), function () use ($symbol, $interval, $limit) {
            $response = Http::timeout(10)->get(self::BYBIT_BASE_URL.'/v5/market/kline', [
                'category' => 'linear',
                'symbol' => $symbol,
                'interval' => $interval,
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();
            if (($data['retCode'] ?? -1) !== 0) {
                return [];
            }

            // Bybit returns newest first — reverse for chronological order
            $list = array_reverse($data['result']['list'] ?? []);

            return array_map(fn (array $candle) => [
                'timestamp' => (int) $candle[0],
                'open' => (float) $candle[1],
                'high' => (float) $candle[2],
                'low' => (float) $candle[3],
                'close' => (float) $candle[4],
                'volume' => (float) $candle[5],
            ], $list);
        });
    }

    // Orchestrates the full screening: fetch tickers → fetch klines → compute indicators → filter
    public function screen(array $filters): array
    {
        $tickers = $this->getMarketTickers();
        if (empty($tickers)) {
            return ['results' => [], 'total' => 0];
        }

        $interval = self::INTERVAL_MAP[$filters['timeframe'] ?? '1D'] ?? 'D';
        $indicatorFilters = $filters['indicators'] ?? [];
        $priceMin = isset($filters['price_min']) ? (float) $filters['price_min'] : null;
        $priceMax = isset($filters['price_max']) ? (float) $filters['price_max'] : null;
        $changeMin = isset($filters['change_min']) ? (float) $filters['change_min'] : null;
        $changeMax = isset($filters['change_max']) ? (float) $filters['change_max'] : null;
        $volumeMin = isset($filters['volume_min']) ? (float) $filters['volume_min'] : null;
        $sortBy = $filters['sort_by'] ?? 'volume24h';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        // Pre-filter tickers by price/change/volume before fetching klines
        $candidates = $this->preFilterTickers($tickers, $priceMin, $priceMax, $changeMin, $changeMax, $volumeMin, $indicatorFilters);

        // If no indicator filters need kline computation, return basic ticker data
        $needsKlines = collect($indicatorFilters)->contains(function ($filter) {
            $def = self::getIndicatorDefinition($filter['key'] ?? '');

            return $def && !in_array($def['type'], ['basic', 'price_change', 'volume_basic', 'turnover_basic']);
        });

        if (!$needsKlines || empty($candidates)) {
            return $this->buildResults($candidates, [], $sortBy, $sortDir);
        }

        // Fetch klines in parallel batches and compute indicators
        $symbolList = array_keys($candidates);
        $indicatorResults = [];

        foreach (array_chunk($symbolList, self::HTTP_POOL_BATCH_SIZE) as $batch) {
            $responses = Http::pool(function ($pool) use ($batch, $interval) {
                foreach ($batch as $symbol) {
                    $cacheKey = "screener_kline_{$symbol}_{$interval}_".self::KLINE_LIMIT;
                    if (Cache::has($cacheKey)) {
                        continue; // Skip cached symbols — we'll read from cache
                    }
                    $pool->as($symbol)
                        ->timeout(10)
                        ->get(self::BYBIT_BASE_URL.'/v5/market/kline', [
                            'category' => 'linear',
                            'symbol' => $symbol,
                            'interval' => $interval,
                            'limit' => self::KLINE_LIMIT,
                        ]);
                }
            });

            // Process pool responses into cache
            foreach ($responses as $symbol => $response) {
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $data = $response->json();
                    if (($data['retCode'] ?? -1) === 0) {
                        $list = array_reverse($data['result']['list'] ?? []);
                        $klines = array_map(fn (array $candle) => [
                            'timestamp' => (int) $candle[0],
                            'open' => (float) $candle[1],
                            'high' => (float) $candle[2],
                            'low' => (float) $candle[3],
                            'close' => (float) $candle[4],
                            'volume' => (float) $candle[5],
                        ], $list);

                        $cacheKey = "screener_kline_{$symbol}_{$interval}_".self::KLINE_LIMIT;
                        Cache::put($cacheKey, $klines, now()->addMinutes(self::KLINE_CACHE_TTL_MINUTES));
                    }
                }
            }

            // Compute indicators for each symbol in this batch
            foreach ($batch as $symbol) {
                $klines = $this->getKlineData($symbol, $interval);
                if (empty($klines)) {
                    unset($candidates[$symbol]);

                    continue;
                }

                $computed = $this->computeIndicators($klines, $indicatorFilters);
                if ($computed === null) {
                    unset($candidates[$symbol]);

                    continue;
                }

                // Check if symbol passes all indicator filters
                if ($this->passesIndicatorFilters($computed, $indicatorFilters)) {
                    $indicatorResults[$symbol] = $computed;
                } else {
                    unset($candidates[$symbol]);
                }
            }
        }

        return $this->buildResults($candidates, $indicatorResults, $sortBy, $sortDir);
    }

    // Pre-filters tickers by price, change %, volume and 'basic' dynamically added filters
    private function preFilterTickers(array $tickers, ?float $priceMin, ?float $priceMax, ?float $changeMin, ?float $changeMax, ?float $volumeMin, array $indicatorFilters = []): array
    {
        return array_filter($tickers, function (array $t) use ($priceMin, $priceMax, $changeMin, $changeMax, $volumeMin, $indicatorFilters) {
            if ($priceMin !== null && $t['lastPrice'] < $priceMin) {
                return false;
            }
            if ($priceMax !== null && $t['lastPrice'] > $priceMax) {
                return false;
            }
            if ($changeMin !== null && $t['price24hPcnt'] < $changeMin) {
                return false;
            }
            if ($changeMax !== null && $t['price24hPcnt'] > $changeMax) {
                return false;
            }
            if ($volumeMin !== null && $t['turnover24h'] < $volumeMin) {
                return false;
            }

            // Apply basic indicator preset filters
            foreach ($indicatorFilters as $filter) {
                $key = $filter['key'] ?? '';
                $def = self::getIndicatorDefinition($key);
                // If it evaluates via klines later, skip it here
                if (!$def || !in_array($def['type'], ['price_change', 'volume_basic', 'turnover_basic'])) {
                    continue;
                }

                $field = $def['field'];
                $value = $t[$field] ?? null;
                if ($value === null) {
                    return false;
                }

                $condition = $filter['condition'] ?? '';

                $passes = match ($def['type']) {
                    'price_change' => match ($condition) {
                        'up_5' => $value >= 0.05,
                        'up_10' => $value >= 0.10,
                        'up_20' => $value >= 0.20,
                        'down_5' => $value <= -0.05,
                        'down_10' => $value <= -0.10,
                        'positive' => $value > 0,
                        'negative' => $value < 0,
                        default => false,
                    },
                    'volume_basic' => match ($condition) {
                        'above_1m' => $value >= 1000000,
                        'above_5m' => $value >= 5000000,
                        'above_10m' => $value >= 10000000,
                        'above_50m' => $value >= 50000000,
                        'above_100m' => $value >= 100000000,
                        default => false,
                    },
                    'turnover_basic' => match ($condition) {
                        'above_1m' => $value >= 1000000,
                        'above_10m' => $value >= 10000000,
                        'above_50m' => $value >= 50000000,
                        'above_100m' => $value >= 100000000,
                        'above_500m' => $value >= 500000000,
                        default => false,
                    },
                    default => false,
                };

                if (!$passes) {
                    return false;
                }
            }

            return true;
        });
    }

    // Computes requested technical indicators from OHLCV kline data (returns current & prev for crossovers)
    public function computeIndicators(array $klines, array $indicatorFilters): ?array
    {
        $count = count($klines);
        if ($count < 2) {
            return null;
        }

        $closes = array_column($klines, 'close');
        $closesPrev = array_slice($closes, 0, -1);

        $highs = array_column($klines, 'high');
        $highsPrev = array_slice($highs, 0, -1);

        $lows = array_column($klines, 'low');
        $lowsPrev = array_slice($lows, 0, -1);

        $volumes = array_column($klines, 'volume');
        $volumesPrev = array_slice($volumes, 0, -1);

        $result = [
            'price' => $closes[$count - 1],
            'price_prev' => $closes[$count - 2],
            'volume' => $volumes[$count - 1],
            'volume_prev' => $volumes[$count - 2],
        ];

        foreach ($indicatorFilters as $filter) {
            $key = $filter['key'] ?? '';
            $def = self::getIndicatorDefinition($key);
            if (!$def || $def['type'] === 'basic') {
                continue;
            }

            $type = $def['type'];
            $period = current(array_filter([$def['period'] ?? null, self::INDICATOR_DEFAULTS[$type]['period'] ?? null, 14]));
            $condition = $filter['condition'] ?? '';

            // Skip basic types
            if (in_array($type, ['basic', 'price_change', 'volume_basic', 'turnover_basic'])) {
                continue;
            }

            switch ($type) {
                case 'rsi':
                    if (!isset($result["rsi_{$period}"])) {
                        $result["rsi_{$period}"] = $this->calculateRSI($closes, $period);
                        $result["rsi_{$period}_prev"] = $this->calculateRSI($closesPrev, $period);
                    }

                    break;

                case 'sma':
                    if (!isset($result["sma_{$period}"])) {
                        $result["sma_{$period}"] = $this->calculateSMA($closes, $period);
                        $result["sma_{$period}_prev"] = $this->calculateSMA($closesPrev, $period);
                    }

                    break;

                case 'ema':
                    if (!isset($result["ema_{$period}"])) {
                        $result["ema_{$period}"] = $this->calculateEMA($closes, $period);
                        $result["ema_{$period}_prev"] = $this->calculateEMA($closesPrev, $period);
                    }

                    break;

                case 'macd':
                    if (!isset($result['macd_line'])) {
                        $macdCurrent = $this->calculateMACD($closes);
                        $macdPrev = $this->calculateMACD($closesPrev);
                        $result['macd_line'] = $macdCurrent['macd'];
                        $result['macd_signal'] = $macdCurrent['signal'];
                        $result['macd_histogram'] = $macdCurrent['histogram'];
                        $result['macd_line_prev'] = $macdPrev['macd'];
                        $result['macd_signal_prev'] = $macdPrev['signal'];
                        $result['macd_histogram_prev'] = $macdPrev['histogram'];
                    }

                    break;

                case 'bb':
                    if (!isset($result['bb_bandwidth'])) {
                        $bbCurrent = $this->calculateBollingerBands($closes, $period);
                        $bbPrev = $this->calculateBollingerBands($closesPrev, $period);
                        $result['bb_upper'] = $bbCurrent['upper'];
                        $result['bb_middle'] = $bbCurrent['middle'];
                        $result['bb_lower'] = $bbCurrent['lower'];
                        $result['bb_bandwidth'] = $bbCurrent['bandwidth'];

                        $result['bb_upper_prev'] = $bbPrev['upper'];
                        $result['bb_middle_prev'] = $bbPrev['middle'];
                        $result['bb_lower_prev'] = $bbPrev['lower'];
                        $result['bb_bandwidth_prev'] = $bbPrev['bandwidth'];
                    }

                    break;

                case 'volume_sma':
                    if (!isset($result["vol_sma_{$period}"])) {
                        $smaCurrent = $this->calculateSMA($volumes, $period);
                        $smaPrev = $this->calculateSMA($volumesPrev, $period);
                        $result["vol_sma_{$period}"] = $smaCurrent;
                        $result["vol_sma_{$period}_prev"] = $smaPrev;

                        $volCur = end($volumes);
                        $result['vol_ratio'] = ($smaCurrent > 0) ? ($volCur / $smaCurrent) : 0;
                    }

                    break;

                case 'support':
                    if (!isset($result["sup_{$period}"])) {
                        $result["sup_{$period}"] = $this->calculateSupport($lows, $period);
                        // We do not strictly need prev for support unless requested, but calculating for consistency
                        $result["sup_{$period}_prev"] = $this->calculateSupport($lowsPrev, $period);
                    }

                    break;

                case 'resistance':
                    if (!isset($result["res_{$period}"])) {
                        $result["res_{$period}"] = $this->calculateResistance($highs, $period);
                        $result["res_{$period}_prev"] = $this->calculateResistance($highsPrev, $period);
                    }

                    break;
            }
        }

        return $result;
    }

    // RSI using Wilder's smoothing method
    private function calculateRSI(array $closes, int $period = 14): ?float
    {
        $count = count($closes);
        if ($count < $period + 1) {
            return null;
        }

        $gains = [];
        $losses = [];

        for ($i = 1; $i < $count; $i++) {
            $change = $closes[$i] - $closes[$i - 1];
            $gains[] = $change > 0 ? $change : 0;
            $losses[] = $change < 0 ? abs($change) : 0;
        }

        // Initial averages
        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        // Wilder's smoothing for remaining periods
        for ($i = $period; $i < count($gains); $i++) {
            $avgGain = (($avgGain * ($period - 1)) + $gains[$i]) / $period;
            $avgLoss = (($avgLoss * ($period - 1)) + $losses[$i]) / $period;
        }

        if ($avgLoss == 0) {
            return 100.0;
        }

        $rs = $avgGain / $avgLoss;

        return round(100 - (100 / (1 + $rs)), 2);
    }

    // Simple Moving Average — returns the latest value
    private function calculateSMA(array $data, int $period = 20): ?float
    {
        $count = count($data);
        if ($count < $period) {
            return null;
        }

        $slice = array_slice($data, -$period);

        return round(array_sum($slice) / $period, 8);
    }

    // Exponential Moving Average — returns the latest value
    private function calculateEMA(array $data, int $period = 12): ?float
    {
        $count = count($data);
        if ($count < $period) {
            return null;
        }

        $multiplier = 2 / ($period + 1);

        // Seed EMA with SMA of first `period` values
        $ema = array_sum(array_slice($data, 0, $period)) / $period;

        for ($i = $period; $i < $count; $i++) {
            $ema = ($data[$i] - $ema) * $multiplier + $ema;
        }

        return round($ema, 8);
    }

    // MACD(12,26,9) — returns macd line, signal, and histogram
    private function calculateMACD(array $closes): array
    {
        $ema12 = $this->calculateEMA($closes, 12);
        $ema26 = $this->calculateEMA($closes, 26);

        if ($ema12 === null || $ema26 === null) {
            return ['macd' => null, 'signal' => null, 'histogram' => null];
        }

        // Build full EMA series for signal line calculation
        $count = count($closes);
        $multiplier12 = 2 / 13;
        $multiplier26 = 2 / 27;

        $ema12Series = array_sum(array_slice($closes, 0, 12)) / 12;
        $ema26Series = array_sum(array_slice($closes, 0, 26)) / 26;
        $macdLine = [];

        for ($i = 26; $i < $count; $i++) {
            // Rebuild EMA12 from index 12 onward
            if ($i === 26) {
                $runEma12 = array_sum(array_slice($closes, 0, 12)) / 12;
                for ($j = 12; $j <= $i; $j++) {
                    $runEma12 = ($closes[$j] - $runEma12) * $multiplier12 + $runEma12;
                }
                $runEma26 = $ema26Series;
                for ($j = 26; $j <= $i; $j++) {
                    $runEma26 = ($closes[$j] - $runEma26) * $multiplier26 + $runEma26;
                }
            } else {
                $runEma12 = ($closes[$i] - $runEma12) * $multiplier12 + $runEma12;
                $runEma26 = ($closes[$i] - $runEma26) * $multiplier26 + $runEma26;
            }

            $macdLine[] = $runEma12 - $runEma26;
        }

        if (count($macdLine) < 9) {
            return ['macd' => round($ema12 - $ema26, 8), 'signal' => null, 'histogram' => null];
        }

        // Signal line = EMA(9) of MACD line
        $signalMultiplier = 2 / 10;
        $signal = array_sum(array_slice($macdLine, 0, 9)) / 9;
        for ($i = 9; $i < count($macdLine); $i++) {
            $signal = ($macdLine[$i] - $signal) * $signalMultiplier + $signal;
        }

        $macdValue = end($macdLine);
        $histogram = $macdValue - $signal;

        return [
            'macd' => round($macdValue, 8),
            'signal' => round($signal, 8),
            'histogram' => round($histogram, 8),
        ];
    }

    // Bollinger Bands(period, stddev multiplier) — returns upper, middle, lower, bandwidth
    private function calculateBollingerBands(array $closes, int $period = 20, float $stddevMult = 2.0): array
    {
        $sma = $this->calculateSMA($closes, $period);
        if ($sma === null) {
            return ['upper' => null, 'middle' => null, 'lower' => null, 'bandwidth' => null];
        }

        $slice = array_slice($closes, -$period);
        $variance = 0;
        foreach ($slice as $val) {
            $variance += ($val - $sma) ** 2;
        }
        $stddev = sqrt($variance / $period);

        $upper = round($sma + ($stddevMult * $stddev), 8);
        $lower = round($sma - ($stddevMult * $stddev), 8);
        $bandwidth = $sma > 0 ? round(($upper - $lower) / $sma * 100, 2) : null;

        return [
            'upper' => $upper,
            'middle' => $sma,
            'lower' => $lower,
            'bandwidth' => $bandwidth,
        ];
    }

    // Support (N-day Low)
    private function calculateSupport(array $lows, int $period = 20): ?float
    {
        $count = count($lows);
        if ($count < $period) {
            return null;
        }
        $slice = array_slice($lows, -$period);

        return min($slice);
    }

    // Resistance (N-day High)
    private function calculateResistance(array $highs, int $period = 20): ?float
    {
        $count = count($highs);
        if ($count < $period) {
            return null;
        }
        $slice = array_slice($highs, -$period);

        return max($slice);
    }

    // Checks if computed indicator values pass predefined condition filters
    private function passesIndicatorFilters(array $computed, array $indicatorFilters): bool
    {
        $priceCur = $computed['price'] ?? 0;
        $pricePrev = $computed['price_prev'] ?? 0;

        foreach ($indicatorFilters as $filter) {
            $key = $filter['key'] ?? '';
            $def = self::getIndicatorDefinition($key);
            if (!$def || in_array($def['type'], ['basic', 'price_change', 'volume_basic', 'turnover_basic'])) {
                continue;
            }

            $type = $def['type'];
            $condition = $filter['condition'] ?? '';
            $period = current(array_filter([$def['period'] ?? null, self::INDICATOR_DEFAULTS[$type]['period'] ?? null, 14]));

            $passes = false;

            switch ($type) {
                case 'sma':
                case 'ema':
                    $maCur = $computed["{$type}_{$period}"] ?? null;
                    $maPrev = $computed["{$type}_{$period}_prev"] ?? null;
                    if ($maCur === null || $maPrev === null) {
                        break;
                    }

                    $passes = match ($condition) {
                        'price_above' => $priceCur > $maCur,
                        'price_below' => $priceCur < $maCur,
                        'price_above_5' => $priceCur > $maCur && $priceCur <= ($maCur * 1.05),
                        'price_below_5' => $priceCur < $maCur && $priceCur >= ($maCur * 0.95),
                        'price_cross_above' => $pricePrev <= $maPrev && $priceCur > $maCur,
                        'price_cross_below' => $pricePrev >= $maPrev && $priceCur < $maCur,
                        default => false,
                    };

                    break;

                case 'rsi':
                    $rsiCur = $computed["rsi_{$period}"] ?? null;
                    $rsiPrev = $computed["rsi_{$period}_prev"] ?? null;
                    if ($rsiCur === null) {
                        break;
                    }

                    $passes = match ($condition) {
                        'oversold' => $rsiCur < 30,
                        'overbought' => $rsiCur > 70,
                        'neutral' => $rsiCur >= 30 && $rsiCur <= 70,
                        'cross_above_30' => $rsiPrev <= 30 && $rsiCur > 30,
                        'cross_below_70' => $rsiPrev >= 70 && $rsiCur < 70,
                        'above_50' => $rsiCur > 50,
                        'below_50' => $rsiCur < 50,
                        default => false,
                    };

                    break;

                case 'macd':
                    $macdCur = $computed['macd_line'] ?? null;
                    $sigCur = $computed['macd_signal'] ?? null;
                    $macdPrev = $computed['macd_line_prev'] ?? null;
                    $sigPrev = $computed['macd_signal_prev'] ?? null;
                    $histCur = $computed['macd_histogram'] ?? null;

                    if ($macdCur === null || $sigCur === null) {
                        break;
                    }

                    $passes = match ($condition) {
                        'bullish_cross' => $macdPrev <= $sigPrev && $macdCur > $sigCur,
                        'bearish_cross' => $macdPrev >= $sigPrev && $macdCur < $sigCur,
                        'hist_positive' => $histCur > 0,
                        'hist_negative' => $histCur < 0,
                        default => false,
                    };

                    break;

                case 'bb':
                    $upper = $computed['bb_upper'] ?? null;
                    $lower = $computed['bb_lower'] ?? null;
                    $middleCur = $computed['bb_middle'] ?? null;
                    $middlePrev = $computed['bb_middle_prev'] ?? null;

                    if ($upper === null) {
                        break;
                    }

                    $passes = match ($condition) {
                        'price_above_upper' => $priceCur > $upper,
                        'price_below_lower' => $priceCur < $lower,
                        'price_cross_middle_above' => $pricePrev <= $middlePrev && $priceCur > $middleCur,
                        default => false,
                    };

                    break;

                case 'volume_sma':
                    $volCur = $computed['volume'] ?? 0;
                    $volSmaCur = $computed["vol_sma_{$period}"] ?? null;

                    if ($volSmaCur === null || $volSmaCur == 0) {
                        break;
                    }

                    $ratio = $volCur / $volSmaCur;

                    $passes = match ($condition) {
                        'vol_spike_2x' => $ratio >= 2.0,
                        'vol_spike_3x' => $ratio >= 3.0,
                        'vol_above_avg' => $ratio > 1.0,
                        default => false,
                    };

                    break;
            }

            if (!$passes) {
                return false;
            }
        }

        return true;
    }

    // Returns the available conditions for an indicator type
    public static function getConditionsForType(string $type): array
    {
        return match ($type) {
            'sma', 'ema' => [
                'price_above' => 'Price is above average',
                'price_below' => 'Price is below average',
                'price_above_5' => 'Price is above and within 5%',
                'price_below_5' => 'Price is below and within 5%',
                'price_cross_above' => 'Price crossed average - above',
                'price_cross_below' => 'Price crossed average - below',
            ],
            'rsi' => [
                'oversold' => 'RSI is Oversold (< 30)',
                'overbought' => 'RSI is Overbought (> 70)',
                'neutral' => 'RSI is Neutral (30-70)',
                'cross_above_30' => 'RSI crossed above 30',
                'cross_below_70' => 'RSI crossed below 70',
                'above_50' => 'RSI is Above 50',
                'below_50' => 'RSI is Below 50',
            ],
            'macd' => [
                'bullish_cross' => 'MACD crossed Signal - bullish',
                'bearish_cross' => 'MACD crossed Signal - bearish',
                'hist_positive' => 'Histogram is positive (> 0)',
                'hist_negative' => 'Histogram is negative (< 0)',
            ],
            'bb' => [
                'price_above_upper' => 'Price is above Upper Band',
                'price_below_lower' => 'Price is below Lower Band',
                'price_cross_middle_above' => 'Price crossed Middle Band - above',
            ],
            'volume_sma' => [
                'vol_spike_2x' => 'Volume spiked (> 2x Average)',
                'vol_spike_3x' => 'Volume spiked (> 3x Average)',
                'vol_above_avg' => 'Volume is above Average',
            ],
            'price_change' => [
                'up_5' => 'Up 5% or more',
                'up_10' => 'Up 10% or more',
                'up_20' => 'Up 20% or more',
                'down_5' => 'Down 5% or more',
                'down_10' => 'Down 10% or more',
                'positive' => 'Positive (Green)',
                'negative' => 'Negative (Red)',
            ],
            'volume_basic' => [
                'above_1m' => 'Above 1M',
                'above_5m' => 'Above 5M',
                'above_10m' => 'Above 10M',
                'above_50m' => 'Above 50M',
                'above_100m' => 'Above 100M',
            ],
            'turnover_basic' => [
                'above_1m' => 'Above 1M USDT',
                'above_10m' => 'Above 10M USDT',
                'above_50m' => 'Above 50M USDT',
                'above_100m' => 'Above 100M USDT',
                'above_500m' => 'Above 500M USDT',
            ],
            'support' => [

                'within_1_pct' => '0% to 1% near support',
                'within_2_pct' => '1% to 2% near support',
                'within_3_pct' => '2% to 3% near support',
                'within_5_pct' => '3% to 5% near support',
                'within_10_pct' => '5% to 10% near support',
                'less_than_5_pct' => '5% or less near support',
                'less_than_10_pct' => '10% or less near support',
                'price_below' => 'Price broke below support',
            ],
            'resistance' => [
                'within_1_pct' => '0% to 1% near resistance',
                'within_2_pct' => '1% to 2% near resistance',
                'within_3_pct' => '2% to 3% near resistance',
                'within_5_pct' => '3% to 5% near resistance',
                'within_10_pct' => '5% to 10% near resistance',
                'less_than_5_pct' => '5% or less near resistance',
                'less_than_10_pct' => '10% or less near resistance',
                'price_above' => 'Price broke above resistance',
            ],
            default => [],
        };
    }

    // Builds the final sorted results array from filtered candidates + indicators
    private function buildResults(array $candidates, array $indicatorResults, string $sortBy, string $sortDir): array
    {
        $results = [];
        foreach ($candidates as $symbol => $ticker) {
            $row = $ticker;
            $row['indicators'] = $indicatorResults[$symbol] ?? [];
            $results[] = $row;
        }

        // Sort results
        usort($results, function (array $a, array $b) use ($sortBy, $sortDir) {
            // First look in root for basic columns, then in indicators. But wait: basic indicator
            // values aren't in indicators[], they are in root. We can just check root first.
            $aVal = $a[$sortBy] ?? $a['indicators'][$sortBy] ?? 0;
            $bVal = $b[$sortBy] ?? $b['indicators'][$sortBy] ?? 0;

            return $sortDir === 'asc' ? $aVal <=> $bVal : $bVal <=> $aVal;
        });

        return ['results' => $results, 'total' => count($results)];
    }

    // Helper to get definition by key
    public static function getIndicatorDefinition(string $key): ?array
    {
        foreach (self::availableIndicators() as $category => $subcategories) {
            foreach ($subcategories as $subcategory => $indicators) {
                foreach ($indicators as $ind) {
                    if ($ind['key'] === $key) {
                        return $ind;
                    }
                }
            }
        }

        return null;
    }

    // Returns indicators organized by category/subcategory
    public static function availableIndicators(): array
    {
        return [
            'Technical' => [
                'Basic' => [
                    ['key' => 'price_change_24h', 'label' => '24h Change %', 'type' => 'price_change', 'field' => 'price24hPcnt'],
                    ['key' => 'volume_24h', 'label' => 'Volume (24h)', 'type' => 'volume_basic', 'field' => 'volume24h'],
                    ['key' => 'turnover_24h', 'label' => 'Turnover (USDT)', 'type' => 'turnover_basic', 'field' => 'turnover24h'],
                    ['key' => 'volume_avg_10', 'label' => 'Volume Average (10 Days)', 'type' => 'volume_sma', 'period' => 10],
                    ['key' => 'volume_avg_20', 'label' => 'Volume Average (20 Days)', 'type' => 'volume_sma', 'period' => 20],
                    ['key' => 'volume_avg_50', 'label' => 'Volume Average (50 Days)', 'type' => 'volume_sma', 'period' => 50],
                    ['key' => 'volume_spike', 'label' => 'Volume Spike', 'type' => 'volume_sma', 'period' => 20, 'field' => 'vol_ratio'],
                ],
                'MA (Simple)' => [
                    ['key' => 'sma_9', 'label' => 'MA (9 Days)', 'type' => 'sma', 'period' => 9],
                    ['key' => 'sma_18', 'label' => 'MA (18 Days)', 'type' => 'sma', 'period' => 18],
                    ['key' => 'sma_20', 'label' => 'MA (20 Days)', 'type' => 'sma', 'period' => 20],
                    ['key' => 'sma_50', 'label' => 'MA (50 Days)', 'type' => 'sma', 'period' => 50],
                    ['key' => 'sma_80', 'label' => 'MA (80 Days)', 'type' => 'sma', 'period' => 80],
                    ['key' => 'sma_100', 'label' => 'MA (100 Days)', 'type' => 'sma', 'period' => 100],
                    ['key' => 'sma_200', 'label' => 'MA (200 Days)', 'type' => 'sma', 'period' => 200],
                ],
                'MA (Exponential)' => [
                    ['key' => 'ema_9', 'label' => 'EMA (9 Days)', 'type' => 'ema', 'period' => 9],
                    ['key' => 'ema_13', 'label' => 'EMA (13 Days)', 'type' => 'ema', 'period' => 13],
                    ['key' => 'ema_20', 'label' => 'EMA (20 Days)', 'type' => 'ema', 'period' => 20],
                    ['key' => 'ema_50', 'label' => 'EMA (50 Days)', 'type' => 'ema', 'period' => 50],
                    ['key' => 'ema_100', 'label' => 'EMA (100 Days)', 'type' => 'ema', 'period' => 100],
                    ['key' => 'ema_200', 'label' => 'EMA (200 Days)', 'type' => 'ema', 'period' => 200],
                ],
                'Support / Resistance' => [
                    ['key' => 'sup_20', 'label' => 'Support (20 Days)', 'type' => 'support', 'period' => 20],
                    ['key' => 'sup_50', 'label' => 'Support (50 Days)', 'type' => 'support', 'period' => 50],
                    ['key' => 'sup_200', 'label' => 'Support (200 Days)', 'type' => 'support', 'period' => 200],
                    ['key' => 'res_20', 'label' => 'Resistance (20 Days)', 'type' => 'resistance', 'period' => 20],
                    ['key' => 'res_50', 'label' => 'Resistance (50 Days)', 'type' => 'resistance', 'period' => 50],
                    ['key' => 'res_200', 'label' => 'Resistance (200 Days)', 'type' => 'resistance', 'period' => 200],
                ],
                'Other Indicators' => [
                    ['key' => 'rsi_7', 'label' => 'RSI (7)', 'type' => 'rsi', 'period' => 7],
                    ['key' => 'rsi_14', 'label' => 'RSI (14)', 'type' => 'rsi', 'period' => 14],
                    ['key' => 'rsi_21', 'label' => 'RSI (21)', 'type' => 'rsi', 'period' => 21],
                    ['key' => 'macd', 'label' => 'MACD (12,26,9)', 'type' => 'macd', 'period' => 0, 'field' => 'macd_histogram'],
                    ['key' => 'macd_signal', 'label' => 'MACD Signal', 'type' => 'macd', 'period' => 0, 'field' => 'macd_signal'],
                    ['key' => 'macd_line', 'label' => 'MACD Line', 'type' => 'macd', 'period' => 0, 'field' => 'macd_line'],
                    ['key' => 'bb_upper', 'label' => 'Bollinger Upper', 'type' => 'bb', 'period' => 20, 'field' => 'bb_upper'],
                    ['key' => 'bb_lower', 'label' => 'Bollinger Lower', 'type' => 'bb', 'period' => 20, 'field' => 'bb_lower'],
                    ['key' => 'bb_bandwidth', 'label' => 'Bollinger Bandwidth', 'type' => 'bb', 'period' => 20, 'field' => 'bb_bandwidth'],
                ],
            ],
        ];
    }

    // Returns available timeframe options
    public static function availableTimeframes(): array
    {
        return [
            '1m' => '1 Minute',
            '3m' => '3 Minutes',
            '5m' => '5 Minutes',
            '15m' => '15 Minutes',
            '30m' => '30 Minutes',
            '1h' => '1 Hour',
            '2h' => '2 Hours',
            '4h' => '4 Hours',
            '6h' => '6 Hours',
            '12h' => '12 Hours',
            '1D' => '1 Day',
            '1W' => '1 Week',
            '1M' => '1 Month',
        ];
    }
}
