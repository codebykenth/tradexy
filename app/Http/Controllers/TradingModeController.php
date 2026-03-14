<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

final class TradingModeController extends Controller
{
    /**
     * Set the account mode (Real or Demo) or Market type (Crypto or PSE) in the session.
     */
    public function update(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        if ($request->has('account_mode')) {
            $mode = $request->input('account_mode', 'real');
            if (in_array($mode, ['real', 'demo', 'all'])) {
                session(['account_mode' => $mode]);
            }
        }

        if ($request->has('market_type')) {
            $market = $request->input('market_type', 'crypto');
            if (in_array($market, ['crypto', 'pse', 'all'])) {
                session(['market_type' => $market]);
            }
        }

        // Clear strategy cache so stats refresh for the new mode/market
        Cache::forget("strategies_user_{$userId}_mode_real");
        Cache::forget("strategies_user_{$userId}_mode_demo");
        Cache::forget("strategies_user_{$userId}_mode_real_market_crypto");
        Cache::forget("strategies_user_{$userId}_mode_demo_market_crypto");
        Cache::forget("strategies_user_{$userId}_mode_real_market_pse");
        Cache::forget("strategies_user_{$userId}_mode_demo_market_pse");

        return back()->with('success', 'Trading mode updated.');
    }
}
