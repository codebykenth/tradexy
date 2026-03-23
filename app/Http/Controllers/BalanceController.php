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
        $page = request()->get('page', 1);
        $version = Cache::get("balances_version_user_{$userId}", now()->timestamp);
        $cacheKey = "balances_v{$version}_u{$userId}_a{$accountMode}_m{$marketMode}_c{$prefCurrency}_p{$page}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($userId, $accountMode, $marketMode, $page) {
            $query = Balance::where('user_id', $userId);

            if ($accountMode !== 'all') {
                $query->where('is_demo', $accountMode === 'demo');
            }

            if ($marketMode !== 'all') {
                $query->where('market', $marketMode);
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

            // Manually create paginator from cached data
            $balances = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            // Transform the collection to include formatted attributes for JS
            $balances->getCollection()->transform(function ($balance) {
                $balance->local_date = \Carbon\Carbon::parse($balance->date)->format('M d, Y');
                $balance->formatted_wallet = \App\Helpers\CurrencyFormatter::format($balance->wallet_balance, $balance->market);
                $balance->formatted_equity = \App\Helpers\CurrencyFormatter::format($balance->total_equity, $balance->market);
                $balance->formatted_pnl = \App\Helpers\CurrencyFormatter::format($balance->cum_realised_pnl, $balance->market);

                return $balance;
            });

            return [
                'balances' => $balances,
            ];
        });
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
