<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

final class TradeBulkController extends Controller
{
    /**
     * Handle bulk actions for trades.
     */
    public function handle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trade_ids' => ['required', 'array'],
            'trade_ids.*' => ['exists:trades,id'],
            'action' => ['required', 'string', 'in:update,delete'],
            'timeframe' => ['nullable', 'string', 'max:10'],
            'strategy_id' => ['nullable', 'exists:strategies,id'],
        ]);

        $tradeIds = $validated['trade_ids'];

        // Ensure user owns all trades
        $ownedTrades = Trade::where('user_id', Auth::id())
            ->whereIn('id', $tradeIds);

        if ($validated['action'] === 'delete') {
            $count = $ownedTrades->count();
            $ownedTrades->delete();

            $this->clearTradeCache();

            return redirect()->back()->with('success', "Successfully deleted {$count} trades.");
        }

        if ($validated['action'] === 'update') {
            $updateData = array_filter([
                'timeframe' => $validated['timeframe'] ?? null,
                'strategy_id' => $validated['strategy_id'] ?? null,
            ]);

            if (empty($updateData)) {
                return redirect()->back()->with('error', 'No bulk fields selected to update.');
            }

            $count = $ownedTrades->count();
            $ownedTrades->update($updateData);

            $this->clearTradeCache();

            return redirect()->back()->with('success', "Successfully updated {$count} trades.");
        }

        return redirect()->back()->with('error', 'Invalid bulk action.');
    }

    /**
     * Clear all trade-related caches for the current user.
     */
    private function clearTradeCache(): void
    {
        $userId = Auth::id();

        // Increment the trades version to invalidate all trade-related caches
        Cache::put("trades_version_user_{$userId}", (string) (now()->timestamp), now()->addDays(30));
    }
}
