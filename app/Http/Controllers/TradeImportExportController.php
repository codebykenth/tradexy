<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TradeImportRequest;
use App\Services\TradeImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class TradeImportExportController extends Controller
{
    public function __construct(
        private readonly TradeImportExportService $importExportService,
    ) {}

    /**
     * Export all trades (or filtered trades) as a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $userId = (int) Auth::id();
        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');

        $filters = [
            'market' => $marketMode,
            'is_demo' => $accountMode === 'all' ? null : ($accountMode === 'demo'),
            'date' => $request->query('date'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'symbol' => $request->query('symbol'),
            'outcome' => $request->query('outcome'),
            'side' => $request->query('side'),
            'strategy_id' => $request->query('strategy_id'),
            'timeframe' => $request->query('timeframe'),
            'has_chart' => $request->boolean('has_chart'),
            'has_ai' => $request->boolean('has_ai'),
        ];

        return $this->importExportService->exportCsv($userId, $filters);
    }

    /**
     * Download the standard CSV template for trade import.
     */
    public function template(): StreamedResponse
    {
        return $this->importExportService->downloadTemplate();
    }

    /**
     * Import trades from an uploaded CSV file.
     */
    public function import(TradeImportRequest $request): RedirectResponse
    {
        $userId = (int) Auth::id();
        $file = $request->file('file');

        $result = $this->importExportService->importCsv($file, $userId);

        if ($result['imported'] === 0 && !empty($result['errors'])) {
            $firstError = $result['errors'][0];

            return redirect()->back()->with('error', "Import failed: {$firstError}");
        }

        $message = "Import complete! Successfully imported {$result['imported']} trades.";
        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} duplicate trades skipped).";
        }

        return redirect()->route('trades.index')->with('success', $message);
    }
}
