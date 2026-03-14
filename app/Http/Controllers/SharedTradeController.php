<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class SharedTradeController extends Controller
{
    // Generates a unique share token for the trade
    public function generate(int $id): RedirectResponse
    {
        $trade = Trade::where('user_id', Auth::id())->findOrFail($id);

        $trade->update(['share_token' => Str::random(32)]);

        return redirect()->route('trades.show', $id)
            ->with('success', 'Share link generated successfully.');
    }

    // Revokes an existing share token
    public function revoke(int $id): RedirectResponse
    {
        $trade = Trade::where('user_id', Auth::id())->findOrFail($id);

        $trade->update(['share_token' => null]);

        return redirect()->route('trades.show', $id)
            ->with('success', 'Share link revoked.');
    }

    // Public read-only view of a shared trade (no auth required)
    public function show(string $token): View
    {
        $trade = Trade::where('share_token', $token)
            ->with(['strategy', 'lessons', 'reasons'])
            ->firstOrFail();

        return view('trades.shared', compact('trade'));
    }
}
