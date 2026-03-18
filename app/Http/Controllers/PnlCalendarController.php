<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PnlCalendarController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Get current month and year from request or default to current
        $currentMonth = $request->input('month', now()->month);
        $currentYear = $request->input('year', now()->year);

        // Validate month and year
        $currentMonth = max(1, min(12, (int) $currentMonth));
        $currentYear = max(2000, min(2100, (int) $currentYear));

        // Calculate previous and next month for navigation
        $prevMonth = $currentMonth - 1;
        $prevYear = $currentYear;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $nextMonth = $currentMonth + 1;
        $nextYear = $currentYear;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');

        $version = Cache::get("trades_version_user_{$userId}", '1');
        $cacheKey = "pnl_calendar_user_{$userId}_mode_{$accountMode}_market_{$marketMode}_{$currentYear}_{$currentMonth}_v{$version}";

        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($userId, $accountMode, $marketMode, $currentMonth, $currentYear) {
            // Fetch trades for the selected month
            $tradeQuery = Trade::where('user_id', $userId)
                ->whereNotNull('close_datetime')
                ->whereYear('close_datetime', $currentYear)
                ->whereMonth('close_datetime', $currentMonth);

            if ($accountMode !== 'all') {
                $tradeQuery->where('is_demo', $accountMode === 'demo');
            }
            if ($marketMode !== 'all') {
                $tradeQuery->where('market', $marketMode);
            }

            $trades = $tradeQuery->get();

            // Check if user has any trades at all
            $hasTradesQuery = Trade::where('user_id', $userId)
                ->whereNotNull('close_datetime');

            if ($accountMode !== 'all') {
                $hasTradesQuery->where('is_demo', $accountMode === 'demo');
            }
            if ($marketMode !== 'all') {
                $hasTradesQuery->where('market', $marketMode);
            }

            $hasTrades = $hasTradesQuery->exists();

            // Group trades by date and calculate daily PnL
            $dailyPnl = $trades->groupBy(fn ($trade) => \Carbon\Carbon::parse($trade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('Y-m-d'))
                ->map(function ($dayTrades) {
                    return (object) [
                        'pnl' => $dayTrades->sum('total_pnl'),
                        'trades_count' => $dayTrades->count(),
                    ];
                });

            // Calculate summary stats
            $totalPnl = $dailyPnl->sum('pnl');
            $winDays = $dailyPnl->filter(fn ($day) => $day->pnl > 0)->count();
            $lossDays = $dailyPnl->filter(fn ($day) => $day->pnl < 0)->count();
            $tradingDays = $winDays + $lossDays;
            $dayWinRate = $tradingDays > 0 ? round(($winDays / $tradingDays) * 100, 1) : 0;

            return compact(
                'dailyPnl',
                'hasTrades',
                'totalPnl',
                'winDays',
                'lossDays',
                'dayWinRate'
            );
        });

        return view('pnl-calendar.index', array_merge($data, compact(
            'currentMonth',
            'currentYear',
            'prevMonth',
            'prevYear',
            'nextMonth',
            'nextYear'
        )));
    }
}
