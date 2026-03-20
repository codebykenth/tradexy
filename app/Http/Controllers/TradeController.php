<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TradeRequest;
use App\Jobs\FileUpload;
use App\Models\Strategy;
use App\Models\Trade;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class TradeController extends Controller
{
    public function __construct(
        private readonly FileService $fileService,
    ) {}

    public function index(Request $request)
    {
        $data = $this->getTradesData();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($data);
        }

        return view('trades.index', $data);
    }

    private function getTradesData(): array
    {
        $userId = Auth::id();
        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');
        $page = request()->get('page', 1);

        $version = Cache::get("trades_version_user_{$userId}", now()->timestamp);
        $cacheKey = "trades_data_user_{$userId}_mode_{$accountMode}_market_{$marketMode}_page_{$page}_v{$version}";

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($userId, $accountMode, $marketMode, $page) {
            $query = Trade::with(['strategy'])
                ->where('user_id', $userId)
                ->select([
                    'id', 'user_id', 'strategy_id', 'symbol', 'market', 'is_demo',
                    'quantity', 'total_pnl', 'close_datetime', 'open_datetime',
                    'avg_entry_price', 'avg_exit_price', 'stop_loss_price', 'take_profit_price',
                    'entry_side', 'exit_side', 'chart_picture',
                ])
                ->selectRaw("CASE WHEN ai_analysis IS NOT NULL AND ai_analysis != '' THEN 1 ELSE 0 END as has_ai_analysis");

            if ($accountMode !== 'all') {
                $query->where('is_demo', $accountMode === 'demo');
            }

            if ($marketMode !== 'all') {
                $query->where('market', $marketMode);
            }

            // Get total count for pagination
            $total = $query->count();

            // Get items for current page
            $perPage = 10;
            $items = (clone $query)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->latest('close_datetime')
                ->get();

            // Manually create paginator from cached data
            $ownedTrades = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            $strategies = Strategy::where('user_id', $userId)->get();

            return compact('ownedTrades', 'strategies');
        });
    }

    public function gallery()
    {
        $userId = Auth::id();
        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');
        $winPage = request()->get('win_page', 1);
        $lossPage = request()->get('loss_page', 1);

        $version = Cache::get("trades_version_user_{$userId}", now()->timestamp);
        $cacheKey = "trades_gallery_user_{$userId}_mode_{$accountMode}_market_{$marketMode}_win_{$winPage}_loss_{$lossPage}_v{$version}";

        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($userId, $accountMode, $marketMode, $winPage, $lossPage) {
            $winningQuery = Trade::with(['strategy', 'reasons'])
                ->where('user_id', $userId)
                ->whereNotNull('chart_picture')
                ->where('total_pnl', '>', 0);

            if ($accountMode !== 'all') {
                $winningQuery->where('is_demo', $accountMode === 'demo');
            }
            if ($marketMode !== 'all') {
                $winningQuery->where('market', $marketMode);
            }

            // Winning trades pagination
            $winTotal = (clone $winningQuery)->count();
            $winPerPage = 10;
            $winItems = (clone $winningQuery)
                ->skip(($winPage - 1) * $winPerPage)
                ->take($winPerPage)
                ->latest('close_datetime')
                ->get();

            $winningTrades = new \Illuminate\Pagination\LengthAwarePaginator(
                $winItems,
                $winTotal,
                $winPerPage,
                $winPage,
                ['path' => request()->url(), 'query' => ['win_page' => $winPage, 'loss_page' => $lossPage]]
            );

            // Losing trades pagination
            $losingQuery = Trade::with(['strategy', 'reasons'])
                ->where('user_id', $userId)
                ->whereNotNull('chart_picture')
                ->where('total_pnl', '<', 0);

            if ($accountMode !== 'all') {
                $losingQuery->where('is_demo', $accountMode === 'demo');
            }
            if ($marketMode !== 'all') {
                $losingQuery->where('market', $marketMode);
            }

            $lossTotal = (clone $losingQuery)->count();
            $lossPerPage = 10;
            $lossItems = (clone $losingQuery)
                ->skip(($lossPage - 1) * $lossPerPage)
                ->take($lossPerPage)
                ->latest('close_datetime')
                ->get();

            $losingTrades = new \Illuminate\Pagination\LengthAwarePaginator(
                $lossItems,
                $lossTotal,
                $lossPerPage,
                $lossPage,
                ['path' => request()->url(), 'query' => ['win_page' => $winPage, 'loss_page' => $lossPage]]
            );

            return compact('winningTrades', 'losingTrades');
        });

        return view('trades.gallery', $data);
    }

    public function create()
    {
        $strategies = Strategy::all();

        return view('trades.create', compact('strategies'));
    }

    public function show(int $id)
    {
        $userId = Auth::id();
        $version = Cache::get("trades_version_user_{$userId}", '1');
        $cacheKey = "trade_show_{$id}_user_{$userId}_v{$version}";

        $trade = Cache::remember($cacheKey, now()->addHours(2), function () use ($id) {
            return $this->findOwnedTrade($id, ['strategy', 'lessons', 'reasons']);
        });

        return view('trades.show', compact('trade'));
    }

    public function edit(int $id)
    {
        $trade = $this->findOwnedTrade($id, ['strategy', 'lessons', 'reasons']);
        $strategies = Strategy::all();

        return view('trades.edit', compact('trade', 'strategies'));
    }

    public function store(TradeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $trade = new Trade([
            'user_id' => Auth::id(),
            'order_id' => Str::random(14),
        ]);

        return $this->persistTrade($trade, $validated, $request);
    }

    public function update(TradeRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $trade = $this->findOwnedTrade($id);

        // Market type is immutable after creation — ignore any submitted value
        unset($validated['market']);

        return $this->persistTrade($trade, $validated, $request);
    }

    public function destroy(int $id): RedirectResponse
    {
        $trade = $this->findOwnedTrade($id);

        if ($trade->chart_picture) {
            $this->fileService->deleteFile($trade->chart_picture);
        }

        $trade->delete();

        $this->clearTradeCache();

        return redirect()->route('trades.index')
            ->with('success', 'Trade deleted successfully.');
    }

    // Finds authenticated user's trade, throws 404 if not found
    private function findOwnedTrade(int $id, array $with = []): Trade
    {
        return Trade::where('user_id', Auth::id())
            ->with($with)
            ->findOrFail($id);
    }

    // Shared persistence logic for store() and update()
    private function persistTrade(Trade $trade, array $validated, TradeRequest $request): RedirectResponse
    {
        $validated = $this->computeDerivedFields($validated, $trade);

        $entryReasons = array_filter($request->input('entry_reason', []));
        $exitReasons = array_filter($request->input('exit_reason', []));
        $lessons = array_filter($request->input('lesson', []));

        $chartPicture = $validated['chart_picture'] ?? null;
        unset($validated['chart_picture'], $validated['entry_reason'], $validated['exit_reason'], $validated['lesson']);

        // Fill and save first to get an ID for the Firebase folder structure
        $trade->fill($validated)->save();

        // Handle chart image logic
        if ($request->boolean('remove_chart_picture')) {
            $this->removeChart($trade);
        } elseif ($request->hasFile('chart_picture')) {
            $this->queueChartUpload($request, $trade);
        }

        $this->syncReasons($trade, $entryReasons, $exitReasons);
        $this->syncLessons($trade, $lessons);

        $this->clearTradeCache();

        $action = $trade->wasRecentlyCreated ? 'created' : 'updated';

        $redirect = redirect()->route('trades.show', $trade->id)
            ->with('success', "Trade {$action} successfully.");

        if ($request->hasFile('chart_picture')) {
            $redirect->with('chart_uploading', true);
        }

        return $redirect;
    }

    // Recalculates server-side derived fields (symbol, entry/exit totals, PSE defaults)
    private function computeDerivedFields(array $validated, Trade $trade): array
    {
        if (isset($validated['symbol'])) {
            $validated['symbol'] = strtoupper($validated['symbol']);
        }

        $market = $validated['market'] ?? $trade->market ?? 'crypto';
        // PSE trades: force long-only, no leverage, aggregate fees
        if ($market === 'pse') {
            $validated['entry_side'] = 'long';
            $validated['exit_side'] = 'short';
            $validated['leverage'] = 1;

            // Sum all PSE fee fields into open_fees + close_fees for unified PnL calc
            $brokerComm = (float) ($validated['broker_commission'] ?? $trade->broker_commission ?? 0);
            $pseTrans = (float) ($validated['pse_trans_fee'] ?? $trade->pse_trans_fee ?? 0);
            $sccp = (float) ($validated['sccp_fee'] ?? $trade->sccp_fee ?? 0);
            $vat = (float) ($validated['pse_vat'] ?? $trade->pse_vat ?? 0);
            $salesTax = (float) ($validated['sales_tax'] ?? $trade->sales_tax ?? 0);
            $totalPseFees = $brokerComm + $pseTrans + $sccp + $vat + $salesTax;

            // Split evenly into open/close fees for the standard PnL calculation
            $validated['open_fees'] = round($totalPseFees / 2, 8);
            $validated['close_fees'] = round($totalPseFees - $validated['open_fees'], 8);
        } else {
            // Crypto trades: clear PSE-specific fee fields
            $validated['broker_commission'] = null;
            $validated['pse_trans_fee'] = null;
            $validated['sccp_fee'] = null;
            $validated['pse_vat'] = null;
            $validated['sales_tax'] = null;
        }

        $entryPrice = (float) ($validated['avg_entry_price'] ?? $trade->avg_entry_price ?? 0);
        $exitPrice = (float) ($validated['avg_exit_price'] ?? $trade->avg_exit_price ?? 0);
        $qty = (float) ($validated['quantity'] ?? $trade->quantity ?? 0);

        if ($entryPrice > 0 && $qty > 0) {
            $validated['cum_entry_value'] = $entryPrice * $qty;
        }

        if ($exitPrice > 0 && $qty > 0) {
            $validated['cum_exit_value'] = $exitPrice * $qty;
        }

        // --- Recalculate PnL Server-side for Data Integrity ---
        $entryValue = (float) ($validated['cum_entry_value'] ?? $trade->cum_entry_value ?? 0);
        $exitValue = (float) ($validated['cum_exit_value'] ?? $trade->cum_exit_value ?? 0);

        if ($entryValue > 0 && $exitValue > 0) {
            $side = strtolower($validated['entry_side'] ?? $trade->entry_side ?? 'long');

            $grossPnl = ($side === 'long') ? ($exitValue - $entryValue) : ($entryValue - $exitValue);

            // Sum all possible fees
            $fees = (float) ($validated['open_fees'] ?? $trade->open_fees ?? 0) + (float) ($validated['close_fees'] ?? $trade->close_fees ?? 0);

            // For PSE, if individual fees are present, prioritize them
            if ($market === 'pse') {
                $pseFees = (float) ($validated['broker_commission'] ?? $trade->broker_commission ?? 0) +
                           (float) ($validated['pse_trans_fee'] ?? $trade->pse_trans_fee ?? 0) +
                           (float) ($validated['sccp_fee'] ?? $trade->sccp_fee ?? 0) +
                           (float) ($validated['pse_vat'] ?? $trade->pse_vat ?? 0) +
                           (float) ($validated['sales_tax'] ?? $trade->sales_tax ?? 0);

                if ($pseFees > 0) {
                    $fees = $pseFees;
                }
            }

            $validated['closed_pnl'] = round($grossPnl, 8);
            $validated['total_pnl'] = round($grossPnl - $fees, 8);
        }

        return $validated;
    }

    /**
     * Delete the chart picture immediately (no queue needed for simple deletion).
     */
    private function removeChart(Trade $trade): void
    {
        if ($trade->chart_picture) {
            $this->fileService->deleteFile($trade->chart_picture);

            $trade->update(['chart_picture' => null]);
        }
    }

    /**
     * Save the file to local storage and dispatch the background upload job.
     */
    private function queueChartUpload(TradeRequest $request, Trade $trade): void
    {
        $file = $request->file('chart_picture');
        if (!$file) {
            return;
        }

        // 1. Move the uploaded file to private local storage temporarily
        $tempPath = $file->store('temp', 'local');

        // 2. Dispatch the job to handle the Firebase upload and old file deletion
        FileUpload::dispatch(
            tempPath: $tempPath,
            originalName: $file->getClientOriginalName(),
            directory: "users/{$trade->user_id}/trades",
            modelClass: Trade::class,
            modelId: (string) $trade->id,
            field: 'chart_picture',
            userId: (string) auth()->id(),
            oldFileUrl: $trade->getOriginal('chart_picture')
        );
    }

    // Deletes then recreates entry/exit reasons (idempotent sync)
    private function syncReasons(Trade $trade, array $entryReasons, array $exitReasons): void
    {
        $trade->reasons()->delete();

        foreach ($entryReasons as $reason) {
            $trade->reasons()->create(['type' => 'entry', 'reason' => $reason]);
        }

        foreach ($exitReasons as $reason) {
            $trade->reasons()->create(['type' => 'exit', 'reason' => $reason]);
        }
    }

    // Deletes then recreates lessons (idempotent sync)
    private function syncLessons(Trade $trade, array $lessons): void
    {
        $trade->lessons()->delete();

        foreach ($lessons as $lesson) {
            $trade->lessons()->create(['lesson' => $lesson, 'category' => 'N/A']);
        }
    }

    /**
     * Clear all possible trade cache permutations for the current user.
     */
    private function clearTradeCache(): void
    {
        $userId = Auth::id();
        $accountModes = ['real', 'demo', 'all'];
        $marketTypes = ['crypto', 'pse', 'forex', 'stocks', 'indices', 'commodities', 'all'];

        // Increment the trades version to invalidate all trade-related caches
        Cache::put("trades_version_user_{$userId}", (string) (now()->timestamp), now()->addDays(30));

        // Clear dashboard cache
        foreach ($accountModes as $mode) {
            foreach ($marketTypes as $market) {
                Cache::forget("dashboard_data_user_{$userId}_mode_{$mode}_market_{$market}");
            }
        }

        // Clear strategies cache (since trade counts affect strategy stats)
        foreach ($accountModes as $mode) {
            foreach ($marketTypes as $market) {
                Cache::forget("strategies_user_{$userId}_mode_{$mode}_market_{$market}");
            }
        }

        // Clear PnL calendar cache for current and adjacent months
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
