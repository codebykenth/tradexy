<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BalanceRequest;
use App\Models\Balance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

final class BalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->getBalancesData();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($data);
        }

        return view('balances.index', $data);
    }

    private function getBalancesData(): array
    {
        $userId = Auth::id();
        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');
        $prefCurrency = session('preferred_currency', 'USD');
        $page = (int) request()->get('page', 1);
        $startDate = request()->get('start_date');
        $endDate = request()->get('end_date');
        $dateFilter = request()->get('date');
        $pnlTrend = request()->get('pnl_trend');
        $minEquity = request()->get('min_equity');
        $maxEquity = request()->get('max_equity');

        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $startDate)) {
            $startDate = null;
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $endDate)) {
            $endDate = null;
        }
        if ($dateFilter && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dateFilter)) {
            $dateFilter = null;
        }
        if ($pnlTrend && !in_array($pnlTrend, ['profit', 'loss', 'breakeven'], true)) {
            $pnlTrend = null;
        }
        $minEquity = is_numeric($minEquity) ? (float) $minEquity : null;
        $maxEquity = is_numeric($maxEquity) ? (float) $maxEquity : null;

        $filterPayload = [
            's' => $startDate,
            'e' => $endDate,
            'd' => $dateFilter,
            'trend' => $pnlTrend,
            'min' => $minEquity,
            'max' => $maxEquity,
        ];
        $filterHash = md5(http_build_query(array_filter($filterPayload, fn ($v) => $v !== null)));

        $version = Cache::get("balances_version_user_{$userId}", now()->timestamp);
        $cacheKey = "balances_v{$version}_u{$userId}_a{$accountMode}_m{$marketMode}_c{$prefCurrency}_p{$page}_f{$filterHash}";

        $cached = Cache::remember($cacheKey, now()->addHours(6), function () use (
            $userId, $accountMode, $marketMode, $page,
            $startDate, $endDate, $dateFilter,
            $pnlTrend, $minEquity, $maxEquity
        ) {
            $query = Balance::where('user_id', $userId);

            if ($accountMode !== 'all') {
                $query->where('is_demo', $accountMode === 'demo');
            }

            if ($marketMode !== 'all') {
                $query->where('market', $marketMode);
            }

            // PnL Trend filtering
            if ($pnlTrend === 'profit') {
                $query->where('cum_realised_pnl', '>', 0);
            } elseif ($pnlTrend === 'loss') {
                $query->where('cum_realised_pnl', '<', 0);
            } elseif ($pnlTrend === 'breakeven') {
                $query->where('cum_realised_pnl', '=', 0);
            }

            // Equity range filtering
            if ($minEquity !== null) {
                $query->where('total_equity', '>=', $minEquity);
            }
            if ($maxEquity !== null) {
                $query->where('total_equity', '<=', $maxEquity);
            }

            // Date filtering
            if ($startDate && $endDate) {
                $start = \Carbon\Carbon::parse($startDate)->startOfDay();
                $end = \Carbon\Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('date', [$start, $end]);
            } elseif ($startDate) {
                $start = \Carbon\Carbon::parse($startDate)->startOfDay();
                $query->where('date', '>=', $start);
            } elseif ($endDate) {
                $end = \Carbon\Carbon::parse($endDate)->endOfDay();
                $query->where('date', '<=', $end);
            } elseif ($dateFilter) {
                $date = \Carbon\Carbon::parse($dateFilter)->toDateString();
                $query->whereDate('date', '=', $date);
            }

            // Get total count for pagination
            $total = (clone $query)->count();

            // Get items for current page
            $perPage = 10;
            $items = (clone $query)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->latest('date')
                ->get();

            // Transform the collection to include formatted attributes for JS
            $items->transform(function ($balance) {
                $balance->local_date = \Carbon\Carbon::parse($balance->date)->format('M d, Y');
                $balance->formatted_wallet = \App\Helpers\CurrencyFormatter::format($balance->wallet_balance, $balance->market);
                $balance->formatted_equity = \App\Helpers\CurrencyFormatter::format($balance->total_equity, $balance->market);
                $balance->formatted_pnl = \App\Helpers\CurrencyFormatter::format($balance->cum_realised_pnl, $balance->market);

                return $balance;
            });

            return compact('items', 'total', 'perPage');
        });

        $balances = new \Illuminate\Pagination\LengthAwarePaginator(
            $cached['items'],
            $cached['total'],
            $cached['perPage'],
            (int) $page,
            ['path' => url()->current(), 'query' => request()->query()]
        );

        $initialBalance = Balance::where('user_id', $userId)->oldest('date')->first();

        return compact('balances', 'initialBalance', 'startDate', 'endDate', 'dateFilter', 'pnlTrend', 'minEquity', 'maxEquity');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('balances.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BalanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Balance::create([
            'user_id' => Auth::id(),
            ...$validated,
        ]);

        $this->clearBalanceCache();

        return redirect()->route('balances.index')->with('success', 'Balance successfully added.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BalanceRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $this->findOwnedBalance($id)->update($validated);

        $this->clearBalanceCache();

        return redirect()->route('balances.index')->with('success', 'Balance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $this->findOwnedBalance($id)->delete();

        $this->clearBalanceCache();

        return redirect()->route('balances.index')->with('success', 'Balance deleted successfully.');
    }

    private function findOwnedBalance(string $id): Balance
    {
        return Balance::where('user_id', Auth::id())->findOrFail($id);
    }

    /**
     * Clear all possible balance cache permutations for the current user.
     */
    private function clearBalanceCache(): void
    {
        $userId = Auth::id();
        Cache::put("balances_version_user_{$userId}", (string) now()->timestamp, now()->addDays(30));
    }
}
