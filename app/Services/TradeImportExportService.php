<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lesson;
use App\Models\Reason;
use App\Models\Strategy;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class TradeImportExportService
{
    /**
     * CSV Header mappings and definitions.
     */
    public const CSV_HEADERS = [
        'order_id',
        'market',
        'symbol',
        'entry_side',
        'exit_side',
        'quantity',
        'avg_entry_price',
        'avg_exit_price',
        'cum_entry_value',
        'cum_exit_value',
        'leverage',
        'open_datetime',
        'close_datetime',
        'timeframe',
        'strategy',
        'take_profit_price',
        'stop_loss_price',
        'entry_emotion',
        'exit_emotion',
        'open_fees',
        'close_fees',
        'broker_commission',
        'pse_trans_fee',
        'sccp_fee',
        'pse_vat',
        'sales_tax',
        'closed_pnl',
        'total_pnl',
        'chart_picture',
        'ai_analysis',
        'entry_reasons',
        'exit_reasons',
        'lessons',
        'is_demo',
    ];

    /**
     * Stream a CSV export for the given user.
     */
    public function exportCsv(int $userId, array $filters = []): StreamedResponse
    {
        $filename = 'trades_export_'.now()->format('Y-m-d_His').'.csv';

        $query = Trade::with(['strategy', 'reasons', 'lessons'])
            ->where('user_id', $userId);

        if (!empty($filters['market']) && $filters['market'] !== 'all') {
            $query->where('market', $filters['market']);
        }

        if (isset($filters['is_demo'])) {
            $query->where('is_demo', (bool) $filters['is_demo']);
        }

        if (!empty($filters['symbol'])) {
            $query->where('symbol', 'LIKE', '%'.trim((string) $filters['symbol']).'%');
        }

        if (!empty($filters['outcome'])) {
            if ($filters['outcome'] === 'win') {
                $query->where('total_pnl', '>', 0);
            } elseif ($filters['outcome'] === 'loss') {
                $query->where('total_pnl', '<', 0);
            } elseif ($filters['outcome'] === 'breakeven') {
                $query->where('total_pnl', '=', 0);
            }
        }

        if (!empty($filters['side']) && in_array($filters['side'], ['long', 'short'], true)) {
            $query->where('entry_side', $filters['side']);
        }

        if (!empty($filters['strategy_id']) && is_numeric($filters['strategy_id'])) {
            $query->where('strategy_id', (int) $filters['strategy_id']);
        }

        if (!empty($filters['timeframe'])) {
            $query->where('timeframe', $filters['timeframe']);
        }

        if (!empty($filters['has_chart'])) {
            $query->whereNotNull('chart_picture')->where('chart_picture', '!=', '');
        }

        if (!empty($filters['has_ai'])) {
            $query->whereNotNull('ai_analysis')->where('ai_analysis', '!=', '');
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $start = Carbon::parse($filters['start_date'], 'Asia/Manila')->startOfDay()->utc();
            $end = Carbon::parse($filters['end_date'], 'Asia/Manila')->endOfDay()->utc();
            $query->whereBetween('close_datetime', [$start, $end]);
        } elseif (!empty($filters['start_date'])) {
            $start = Carbon::parse($filters['start_date'], 'Asia/Manila')->startOfDay()->utc();
            $query->where('close_datetime', '>=', $start);
        } elseif (!empty($filters['end_date'])) {
            $end = Carbon::parse($filters['end_date'], 'Asia/Manila')->endOfDay()->utc();
            $query->where('close_datetime', '<=', $end);
        } elseif (!empty($filters['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['date'])) {
            $start = Carbon::createFromFormat('Y-m-d', $filters['date'], 'Asia/Manila')->startOfDay()->utc();
            $end = Carbon::createFromFormat('Y-m-d', $filters['date'], 'Asia/Manila')->endOfDay()->utc();
            $query->whereBetween('close_datetime', [$start, $end]);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // Add UTF-8 BOM for seamless Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Output column headers
            fputcsv($handle, self::CSV_HEADERS);

            $query->chunk(100, function ($trades) use ($handle): void {
                /** @var Trade $trade */
                foreach ($trades as $trade) {
                    $entryReasons = $trade->reasons->where('type', 'entry')->pluck('reason')->implode('; ');
                    $exitReasons = $trade->reasons->where('type', 'exit')->pluck('reason')->implode('; ');
                    $lessons = $trade->lessons->pluck('lesson')->implode('; ');

                    fputcsv($handle, [
                        $trade->order_id,
                        $trade->market,
                        $trade->symbol,
                        $trade->entry_side,
                        $trade->exit_side,
                        $trade->quantity,
                        $trade->avg_entry_price,
                        $trade->avg_exit_price,
                        $trade->cum_entry_value,
                        $trade->cum_exit_value,
                        $trade->leverage,
                        $trade->open_datetime ? Carbon::parse($trade->open_datetime)->format('Y-m-d H:i:s') : '',
                        $trade->close_datetime ? Carbon::parse($trade->close_datetime)->format('Y-m-d H:i:s') : '',
                        $trade->timeframe,
                        $trade->strategy instanceof Strategy ? $trade->strategy->name : '',
                        $trade->take_profit_price,
                        $trade->stop_loss_price,
                        $trade->entry_emotion,
                        $trade->exit_emotion,
                        $trade->open_fees,
                        $trade->close_fees,
                        $trade->broker_commission,
                        $trade->pse_trans_fee,
                        $trade->sccp_fee,
                        $trade->pse_vat,
                        $trade->sales_tax,
                        $trade->closed_pnl,
                        $trade->total_pnl,
                        $trade->chart_picture,
                        $trade->ai_analysis,
                        $entryReasons,
                        $exitReasons,
                        $lessons,
                        $trade->is_demo ? '1' : '0',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Download sample CSV template.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $filename = 'tradexy_import_template.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, self::CSV_HEADERS);

            // Sample Row 1: Crypto Trade
            fputcsv($handle, [
                'ORD'.strtoupper(Str::random(10)),
                'crypto',
                'BTCUSDT',
                'long',
                'short',
                '0.25',
                '64500.00',
                '66200.00',
                '16125.00',
                '16550.00',
                '5',
                now()->subDays(2)->format('Y-m-d 10:00:00'),
                now()->subDays(1)->format('Y-m-d 16:30:00'),
                '1hr',
                'Trend Following',
                '67000.00',
                '63800.00',
                'confidence',
                'confidence',
                '3.22',
                '3.31',
                '',
                '',
                '',
                '',
                '',
                '425.00',
                '418.47',
                'https://storage.googleapis.com/example-bucket/charts/sample1.png',
                'Strong bullish continuation with 4H support confirmation. Managed risk well.',
                'Bullish breakout on 4H; Key support retest',
                'Hit major resistance level',
                'Followed trade plan disciplined; Good risk-reward',
                '0',
            ]);

            // Sample Row 2: PSE Stock Trade
            fputcsv($handle, [
                'ORD'.strtoupper(Str::random(10)),
                'pse',
                'ALI',
                'long',
                'short',
                '1000',
                '29.50',
                '32.00',
                '29500.00',
                '32000.00',
                '1',
                now()->subDays(5)->format('Y-m-d 09:35:00'),
                now()->subDays(3)->format('Y-m-d 14:45:00'),
                '1d',
                'Breakout Strategy',
                '33.50',
                '28.50',
                'confidence',
                'hope',
                '',
                '',
                '153.75',
                '3.08',
                '6.15',
                '18.45',
                '192.00',
                '2500.00',
                '2126.57',
                'https://storage.googleapis.com/example-bucket/charts/sample2.png',
                'Cup and handle chart pattern breakout on above-average PSE volume.',
                'Cup and handle breakout; High volume',
                'Trailing stop hit',
                'Patience pays off on swing trades',
                '0',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Import trades from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: int, errors: array<string>}
     */
    public function importCsv(UploadedFile $file, int $userId): array
    {
        $path = $file->getRealPath();
        if (!$path || !file_exists($path)) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['Unable to read uploaded file.'],
            ];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['Failed to open uploaded file.'],
            ];
        }

        // Read and strip BOM if present
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['The uploaded CSV file is empty.'],
            ];
        }

        $bom = pack('CCC', 0xEF, 0xBB, 0xBF);
        if (str_starts_with($firstLine, $bom)) {
            $firstLine = substr($firstLine, 3);
        }

        $headerRow = str_getcsv($firstLine);
        if ($headerRow === [null] || count(array_filter($headerRow)) === 0) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['Could not parse CSV column headers.'],
            ];
        }

        // Normalize header row to lowercase trimmed strings
        $normalizedHeaders = array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
        $headerMap = array_flip($normalizedHeaders);

        // Required column validation
        $requiredFields = ['symbol', 'avg_entry_price', 'quantity', 'open_datetime'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($headerMap[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['Missing required columns: '.implode(', ', $missingFields)],
            ];
        }

        // Cache user strategies for fast lookup
        $strategies = Strategy::where('user_id', $userId)->get()->keyBy(function (Strategy $s) {
            return strtolower(trim($s->name));
        });

        // Pre-fetch existing order IDs to avoid duplicate insertions
        $existingOrderIds = Trade::where('user_id', $userId)
            ->pluck('order_id')
            ->flip()
            ->toArray();

        // Index existing trade signatures (market + symbol + open_datetime + avg_entry_price + quantity + entry_side)
        $existingSignatures = [];
        Trade::where('user_id', $userId)
            ->select(['symbol', 'open_datetime', 'avg_entry_price', 'quantity', 'market', 'entry_side'])
            ->chunk(500, function ($trades) use (&$existingSignatures): void {
                foreach ($trades as $t) {
                    $sig = sprintf(
                        '%s_%s_%s_%s_%s_%s',
                        strtolower((string) $t->market),
                        strtoupper((string) $t->symbol),
                        $t->open_datetime ? Carbon::parse($t->open_datetime)->format('Y-m-d H:i:s') : '',
                        (string) (float) $t->avg_entry_price,
                        (string) (float) $t->quantity,
                        strtolower((string) $t->entry_side)
                    );
                    $existingSignatures[$sig] = true;
                }
            });

        $importedCount = 0;
        $skippedCount = 0;
        $rowErrors = [];
        $rowNumber = 1;

        $tradesBatch = [];
        $reasonsBatch = [];
        $lessonsBatch = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                continue;
            }

            $getValue = function (string $key, mixed $default = null) use ($row, $headerMap): mixed {
                if (isset($headerMap[$key]) && isset($row[$headerMap[$key]])) {
                    $val = trim((string) $row[$headerMap[$key]]);

                    return $val !== '' ? $val : $default;
                }

                return $default;
            };

            $symbol = strtoupper((string) $getValue('symbol', ''));
            $market = strtolower((string) $getValue('market', 'crypto'));
            if (!in_array($market, ['crypto', 'pse'], true)) {
                $market = 'crypto';
            }

            $entryPrice = (float) $getValue('avg_entry_price', 0);
            $qty = (float) $getValue('quantity', 0);
            $openDatetimeRaw = $getValue('open_datetime');

            if ($symbol === '' || $entryPrice <= 0 || $qty <= 0 || !$openDatetimeRaw) {
                $rowErrors[] = "Row {$rowNumber}: Invalid required data (symbol, entry price, quantity, or open datetime).";

                continue;
            }

            try {
                $openDatetime = Carbon::parse($openDatetimeRaw)->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                $rowErrors[] = "Row {$rowNumber}: Invalid open_datetime format ('{$openDatetimeRaw}').";

                continue;
            }

            $closeDatetimeRaw = $getValue('close_datetime');
            $closeDatetime = null;
            if ($closeDatetimeRaw) {
                try {
                    $closeDatetime = Carbon::parse($closeDatetimeRaw)->format('Y-m-d H:i:s');
                } catch (\Throwable) {
                    $closeDatetime = null;
                }
            }

            $entrySide = strtolower((string) $getValue('entry_side', 'long'));
            if ($market === 'pse') {
                $entrySide = 'long';
            }

            // Build trade signature for duplicate detection
            $tradeSignature = sprintf(
                '%s_%s_%s_%s_%s_%s',
                $market,
                $symbol,
                $openDatetime,
                (string) $entryPrice,
                (string) $qty,
                $entrySide
            );

            $orderId = (string) $getValue('order_id', '');

            // Deduplicate: If duplicate exists in DB or was already seen earlier in this CSV, keep only 1 and skip duplicates
            if (($orderId !== '' && isset($existingOrderIds[$orderId])) || isset($existingSignatures[$tradeSignature])) {
                $skippedCount++;

                continue;
            }

            if ($orderId === '') {
                $orderId = Str::random(14);
            }

            // Mark as seen so any subsequent identical rows in the CSV are skipped
            $existingOrderIds[$orderId] = true;
            $existingSignatures[$tradeSignature] = true;

            $strategyName = $getValue('strategy');
            $strategyId = null;
            if ($strategyName && isset($strategies[strtolower((string) $strategyName)])) {
                $strategyId = $strategies[strtolower((string) $strategyName)]->id;
            }

            $exitSide = strtolower((string) $getValue('exit_side', $entrySide === 'long' ? 'short' : 'long'));
            $leverage = (float) $getValue('leverage', 1);

            $exitPrice = (float) $getValue('avg_exit_price', 0);
            $cumEntryValue = $entryPrice * $qty;
            $cumExitValue = $exitPrice > 0 ? ($exitPrice * $qty) : 0;

            // Derived calculations
            $openFees = (float) $getValue('open_fees', 0);
            $closeFees = (float) $getValue('close_fees', 0);
            $brokerCommission = $getValue('broker_commission') !== null ? (float) $getValue('broker_commission') : null;
            $pseTransFee = $getValue('pse_trans_fee') !== null ? (float) $getValue('pse_trans_fee') : null;
            $sccpFee = $getValue('sccp_fee') !== null ? (float) $getValue('sccp_fee') : null;
            $pseVat = $getValue('pse_vat') !== null ? (float) $getValue('pse_vat') : null;
            $salesTax = $getValue('sales_tax') !== null ? (float) $getValue('sales_tax') : null;

            if ($market === 'pse') {
                $entrySide = 'long';
                $exitSide = 'short';
                $leverage = 1;

                $totalPseFees = (float) ($brokerCommission ?? 0) +
                                (float) ($pseTransFee ?? 0) +
                                (float) ($sccpFee ?? 0) +
                                (float) ($pseVat ?? 0) +
                                (float) ($salesTax ?? 0);

                if ($totalPseFees > 0) {
                    $openFees = round($totalPseFees / 2, 8);
                    $closeFees = round($totalPseFees - $openFees, 8);
                }
            }

            $grossPnl = 0.0;
            $totalPnl = 0.0;
            if ($cumEntryValue > 0 && $cumExitValue > 0) {
                $grossPnl = ($entrySide === 'long') ? ($cumExitValue - $cumEntryValue) : ($cumEntryValue - $cumExitValue);
                $totalFees = ($market === 'pse' && ($brokerCommission || $pseTransFee || $sccpFee || $pseVat || $salesTax))
                    ? ($totalPseFees ?? ($openFees + $closeFees))
                    : ($openFees + $closeFees);
                $totalPnl = $grossPnl - $totalFees;
            }

            $isDemo = (bool) filter_var($getValue('is_demo', '0'), FILTER_VALIDATE_BOOLEAN);
            $now = now();

            $tradeRecord = [
                'user_id' => $userId,
                'strategy_id' => $strategyId,
                'order_id' => $orderId,
                'market' => $market,
                'symbol' => $symbol,
                'entry_side' => $entrySide,
                'exit_side' => $exitSide,
                'quantity' => $qty,
                'cum_entry_value' => $cumEntryValue,
                'cum_exit_value' => $cumExitValue,
                'avg_entry_price' => $entryPrice,
                'avg_exit_price' => $exitPrice > 0 ? $exitPrice : null,
                'entry_emotion' => $getValue('entry_emotion'),
                'exit_emotion' => $getValue('exit_emotion'),
                'take_profit_price' => $getValue('take_profit_price') !== null ? (float) $getValue('take_profit_price') : null,
                'stop_loss_price' => $getValue('stop_loss_price') !== null ? (float) $getValue('stop_loss_price') : null,
                'timeframe' => $getValue('timeframe', '1hr'),
                'leverage' => $leverage,
                'open_fees' => $openFees > 0 ? $openFees : null,
                'close_fees' => $closeFees > 0 ? $closeFees : null,
                'broker_commission' => $brokerCommission,
                'pse_trans_fee' => $pseTransFee,
                'sccp_fee' => $sccpFee,
                'pse_vat' => $pseVat,
                'sales_tax' => $salesTax,
                'closed_pnl' => round($grossPnl, 8),
                'total_pnl' => round($totalPnl, 8),
                'chart_picture' => $getValue('chart_picture') ?? $getValue('chart_url'),
                'ai_analysis' => $getValue('ai_analysis'),
                'open_datetime' => $openDatetime,
                'close_datetime' => $closeDatetime ?? $openDatetime,
                'is_demo' => $isDemo,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Extract reasons and lessons
            $entryReasonsRaw = (string) $getValue('entry_reasons', '');
            $exitReasonsRaw = (string) $getValue('exit_reasons', '');
            $lessonsRaw = (string) $getValue('lessons', '');

            $tradesBatch[$orderId] = $tradeRecord;
            $reasonsBatch[$orderId] = [
                'entry' => $this->parseDelimitedList($entryReasonsRaw),
                'exit' => $this->parseDelimitedList($exitReasonsRaw),
            ];
            $lessonsBatch[$orderId] = $this->parseDelimitedList($lessonsRaw);

            // Process in chunks of 50 to prevent excessive memory/queries
            if (count($tradesBatch) >= 50) {
                $this->persistImportChunk($userId, $tradesBatch, $reasonsBatch, $lessonsBatch);
                $importedCount += count($tradesBatch);
                $tradesBatch = [];
                $reasonsBatch = [];
                $lessonsBatch = [];
            }
        }

        fclose($handle);

        if (!empty($tradesBatch)) {
            $this->persistImportChunk($userId, $tradesBatch, $reasonsBatch, $lessonsBatch);
            $importedCount += count($tradesBatch);
        }

        if ($importedCount > 0) {
            $this->clearTradeCache($userId);
        }

        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => array_slice($rowErrors, 0, 10), // return up to first 10 row errors
        ];
    }

    /**
     * Persist a batch of trades and their relations into the database.
     */
    private function persistImportChunk(
        int $userId,
        array $tradesBatch,
        array $reasonsBatch,
        array $lessonsBatch
    ): void {
        if (empty($tradesBatch)) {
            return;
        }

        DB::transaction(function () use ($userId, $tradesBatch, $reasonsBatch, $lessonsBatch): void {
            Trade::insert(array_values($tradesBatch));

            $newTradeIds = Trade::where('user_id', $userId)
                ->whereIn('order_id', array_keys($tradesBatch))
                ->pluck('id', 'order_id');

            $reasonsToInsert = [];
            $lessonsToInsert = [];
            $now = now();

            foreach ($tradesBatch as $orderId => $tradeData) {
                $tradeId = $newTradeIds[$orderId] ?? null;
                if (!$tradeId) {
                    continue;
                }

                if (!empty($reasonsBatch[$orderId]['entry'])) {
                    foreach ($reasonsBatch[$orderId]['entry'] as $reasonText) {
                        $reasonsToInsert[] = [
                            'trade_id' => $tradeId,
                            'type' => 'entry',
                            'reason' => mb_substr($reasonText, 0, 255),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($reasonsBatch[$orderId]['exit'])) {
                    foreach ($reasonsBatch[$orderId]['exit'] as $reasonText) {
                        $reasonsToInsert[] = [
                            'trade_id' => $tradeId,
                            'type' => 'exit',
                            'reason' => mb_substr($reasonText, 0, 255),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($lessonsBatch[$orderId])) {
                    foreach ($lessonsBatch[$orderId] as $lessonText) {
                        $lessonsToInsert[] = [
                            'trade_id' => $tradeId,
                            'lesson' => mb_substr($lessonText, 0, 255),
                            'category' => 'import',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            if (!empty($reasonsToInsert)) {
                foreach (array_chunk($reasonsToInsert, 100) as $chunk) {
                    Reason::insert($chunk);
                }
            }

            if (!empty($lessonsToInsert)) {
                foreach (array_chunk($lessonsToInsert, 100) as $chunk) {
                    Lesson::insert($chunk);
                }
            }
        });
    }

    /**
     * Parse semicolon, comma, or pipe-delimited string into cleaned array.
     *
     * @return array<string>
     */
    private function parseDelimitedList(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        // Split by semicolon or pipe first, then fallback to comma if neither present
        $delimiter = str_contains($raw, ';') ? ';' : (str_contains($raw, '|') ? '|' : ',');
        $parts = explode($delimiter, $raw);

        $results = [];
        foreach ($parts as $part) {
            $cleaned = trim($part);
            if ($cleaned !== '') {
                $results[] = $cleaned;
            }
        }

        return $results;
    }

    /**
     * Clear all trade cache keys for the user.
     */
    private function clearTradeCache(int $userId): void
    {
        $accountModes = ['real', 'demo', 'all'];
        $marketTypes = ['crypto', 'pse', 'forex', 'stocks', 'indices', 'commodities', 'all'];

        Cache::put("trades_version_user_{$userId}", (string) (now()->timestamp), now()->addDays(30));

        foreach ($accountModes as $mode) {
            foreach ($marketTypes as $market) {
                Cache::forget("dashboard_data_user_{$userId}_mode_{$mode}_market_{$market}");
            }
        }

        Cache::put("strategies_version_user_{$userId}", (string) (now()->timestamp), now()->addDays(30));

        $now = now();
        foreach ([-1, 0, 1] as $monthOffset) {
            $date = $now->copy()->addMonths($monthOffset);
            foreach ($accountModes as $mode) {
                foreach ($marketTypes as $market) {
                    Cache::forget("pnl_calendar_user_{$userId}_mode_{$mode}_market_{$market}_{$date->year}_{$date->month}");
                }
            }
        }
    }
}
