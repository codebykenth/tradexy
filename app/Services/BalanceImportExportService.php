<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Balance;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BalanceImportExportService
{
    /**
     * CSV Header mappings and definitions for balances.
     */
    public const CSV_HEADERS = [
        'date',
        'market',
        'wallet_balance',
        'total_equity',
        'cum_realised_pnl',
        'is_demo',
    ];

    /**
     * Stream a CSV export of balances for the given user.
     */
    public function exportCsv(int $userId, array $filters = []): StreamedResponse
    {
        $filename = 'balances_export_'.now()->format('Y-m-d_His').'.csv';

        $query = Balance::where('user_id', $userId);

        if (!empty($filters['market']) && $filters['market'] !== 'all') {
            $query->where('market', $filters['market']);
        }

        if (isset($filters['is_demo'])) {
            $query->where('is_demo', (bool) $filters['is_demo']);
        }

        if (!empty($filters['pnl_trend'])) {
            if ($filters['pnl_trend'] === 'profit') {
                $query->where('cum_realised_pnl', '>', 0);
            } elseif ($filters['pnl_trend'] === 'loss') {
                $query->where('cum_realised_pnl', '<', 0);
            } elseif ($filters['pnl_trend'] === 'breakeven') {
                $query->where('cum_realised_pnl', '=', 0);
            }
        }

        if (isset($filters['min_equity']) && is_numeric($filters['min_equity'])) {
            $query->where('total_equity', '>=', (float) $filters['min_equity']);
        }

        if (isset($filters['max_equity']) && is_numeric($filters['max_equity'])) {
            $query->where('total_equity', '<=', (float) $filters['max_equity']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $start = Carbon::parse($filters['start_date'])->startOfDay();
            $end = Carbon::parse($filters['end_date'])->endOfDay();
            $query->whereBetween('date', [$start, $end]);
        } elseif (!empty($filters['start_date'])) {
            $start = Carbon::parse($filters['start_date'])->startOfDay();
            $query->where('date', '>=', $start);
        } elseif (!empty($filters['end_date'])) {
            $end = Carbon::parse($filters['end_date'])->endOfDay();
            $query->where('date', '<=', $end);
        } elseif (!empty($filters['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['date'])) {
            $date = Carbon::parse($filters['date'])->toDateString();
            $query->whereDate('date', '=', $date);
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

            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, self::CSV_HEADERS);

            $query->latest('date')->chunk(100, function ($balances) use ($handle): void {
                /** @var Balance $balance */
                foreach ($balances as $balance) {
                    fputcsv($handle, [
                        $balance->date ? Carbon::parse($balance->date)->format('Y-m-d') : '',
                        $balance->market ?? 'crypto',
                        $balance->wallet_balance,
                        $balance->total_equity,
                        $balance->cum_realised_pnl,
                        $balance->is_demo ? '1' : '0',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Download sample CSV template for balances.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $filename = 'tradexy_balance_import_template.csv';

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

            // Sample Row 1: Crypto Balance
            fputcsv($handle, [
                now()->subDays(2)->format('Y-m-d'),
                'crypto',
                '10000.00',
                '10450.00',
                '450.00',
                '0',
            ]);

            // Sample Row 2: PSE Stock Balance
            fputcsv($handle, [
                now()->subDays(1)->format('Y-m-d'),
                'pse',
                '150000.00',
                '158500.00',
                '8500.00',
                '0',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Import balances from an uploaded CSV file.
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

        $normalizedHeaders = array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
        $headerMap = array_flip($normalizedHeaders);

        // Required columns for balance import
        $requiredFields = ['date', 'wallet_balance', 'total_equity', 'cum_realised_pnl'];
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

        // Index existing balances by unique signature (market + date + is_demo)
        $existingSignatures = [];
        Balance::where('user_id', $userId)
            ->select(['market', 'date', 'is_demo'])
            ->chunk(500, function ($balances) use (&$existingSignatures): void {
                foreach ($balances as $b) {
                    $dateStr = $b->date ? Carbon::parse($b->date)->format('Y-m-d') : '';
                    $sig = sprintf('%s_%s_%s', strtolower((string) $b->market), $dateStr, $b->is_demo ? '1' : '0');
                    $existingSignatures[$sig] = true;
                }
            });

        $importedCount = 0;
        $skippedCount = 0;
        $rowErrors = [];
        $rowNumber = 1;
        $balancesBatch = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

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

            $dateRaw = $getValue('date');
            if (!$dateRaw) {
                $rowErrors[] = "Row {$rowNumber}: Missing date value.";

                continue;
            }

            try {
                $date = Carbon::parse($dateRaw)->format('Y-m-d H:i:s');
                $dateCalendar = Carbon::parse($dateRaw)->format('Y-m-d');
            } catch (\Throwable) {
                $rowErrors[] = "Row {$rowNumber}: Invalid date format ('{$dateRaw}').";

                continue;
            }

            $walletBalance = $getValue('wallet_balance');
            $totalEquity = $getValue('total_equity');
            $cumPnl = $getValue('cum_realised_pnl');

            if ($walletBalance === null || $totalEquity === null || $cumPnl === null || !is_numeric($walletBalance) || !is_numeric($totalEquity) || !is_numeric($cumPnl)) {
                $rowErrors[] = "Row {$rowNumber}: Wallet balance, total equity, and cumulative PnL must be numeric.";

                continue;
            }

            $market = strtolower((string) $getValue('market', 'crypto'));
            if (!in_array($market, ['crypto', 'pse'], true)) {
                $market = 'crypto';
            }

            $isDemo = (bool) filter_var($getValue('is_demo', '0'), FILTER_VALIDATE_BOOLEAN);

            // Deduplication: Only 1 balance entry per (user, market, calendar date, demo status)
            $signature = sprintf('%s_%s_%s', $market, $dateCalendar, $isDemo ? '1' : '0');
            if (isset($existingSignatures[$signature])) {
                $skippedCount++;

                continue;
            }

            $existingSignatures[$signature] = true;
            $now = now();

            $balancesBatch[] = [
                'user_id' => $userId,
                'date' => $date,
                'market' => $market,
                'wallet_balance' => round((float) $walletBalance, 8),
                'total_equity' => round((float) $totalEquity, 8),
                'cum_realised_pnl' => round((float) $cumPnl, 8),
                'is_demo' => $isDemo,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($balancesBatch) >= 50) {
                $this->persistBatch($balancesBatch);
                $importedCount += count($balancesBatch);
                $balancesBatch = [];
            }
        }

        fclose($handle);

        if (!empty($balancesBatch)) {
            $this->persistBatch($balancesBatch);
            $importedCount += count($balancesBatch);
        }

        if ($importedCount > 0) {
            $this->clearBalanceCache($userId);
        }

        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => array_slice($rowErrors, 0, 10),
        ];
    }

    /**
     * Persist a batch of balances into database.
     */
    private function persistBatch(array $balances): void
    {
        if (empty($balances)) {
            return;
        }

        DB::transaction(function () use ($balances): void {
            Balance::insert($balances);
        });
    }

    /**
     * Clear all balance and related caches for the user.
     */
    private function clearBalanceCache(int $userId): void
    {
        $accountModes = ['real', 'demo', 'all'];
        $marketTypes = ['crypto', 'pse', 'forex', 'stocks', 'indices', 'commodities', 'all'];

        Cache::put("balances_version_user_{$userId}", (string) (now()->timestamp), now()->addDays(30));
        Cache::put("trades_version_user_{$userId}", (string) (now()->timestamp), now()->addDays(30));

        foreach ($accountModes as $mode) {
            foreach ($marketTypes as $market) {
                Cache::forget("dashboard_data_user_{$userId}_mode_{$mode}_market_{$market}");
            }
        }
    }
}
