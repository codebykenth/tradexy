<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BalanceImportRequest;
use App\Services\BalanceImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BalanceImportExportController extends Controller
{
    public function __construct(
        private readonly BalanceImportExportService $importExportService,
    ) {}

    /**
     * Export all balances as CSV.
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
            'pnl_trend' => $request->query('pnl_trend'),
            'min_equity' => $request->query('min_equity'),
            'max_equity' => $request->query('max_equity'),
        ];

        return $this->importExportService->exportCsv($userId, $filters);
    }

    /**
     * Download the standard CSV template for balance import.
     */
    public function template(): StreamedResponse
    {
        return $this->importExportService->downloadTemplate();
    }

    /**
     * Import balances from an uploaded CSV file.
     */
    public function import(BalanceImportRequest $request): RedirectResponse
    {
        $userId = (int) Auth::id();
        $file = $request->file('file');

        $result = $this->importExportService->importCsv($file, $userId);

        if ($result['imported'] === 0 && !empty($result['errors'])) {
            $firstError = $result['errors'][0];

            return redirect()->back()->with('error', "Import failed: {$firstError}");
        }

        $message = "Import complete! Successfully imported {$result['imported']} balance entries.";
        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} duplicate entries skipped).";
        }

        return redirect()->route('balances.index')->with('success', $message);
    }
}
