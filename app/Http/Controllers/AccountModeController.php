<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AccountModeController extends Controller
{
    /**
     * Set the account mode (Real or Demo) in the session.
     */
    public function update(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'real');
        
        if (!in_array($mode, ['real', 'demo'])) {
            $mode = 'real';
        }

        session(['account_mode' => $mode]);

        // Clear strategy cache so stats refresh for the new mode
        \Illuminate\Support\Facades\Cache::forget("strategies_user_" . \Illuminate\Support\Facades\Auth::id() . "_mode_real");
        \Illuminate\Support\Facades\Cache::forget("strategies_user_" . \Illuminate\Support\Facades\Auth::id() . "_mode_demo");

        return back()->with('success', 'Account mode switched to ' . ucfirst($mode) . '.');
    }
}
