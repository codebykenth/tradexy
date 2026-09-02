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
    public function generate(int $id)
    {
        $trade = Trade::where('user_id', Auth::id())->findOrFail($id);

        $trade->update(['share_token' => Str::random(32)]);

        if (request()->wantsJson()) {
            session()->flash('success', 'Share link generated & copied to clipboard 🎉');

            return response()->json([
                'url' => route('trades.shared', $trade->share_token),
            ]);
        }

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
