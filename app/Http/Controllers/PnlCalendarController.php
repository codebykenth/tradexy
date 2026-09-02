<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Strategy;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final class PnlCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) Auth::id();

        // Get current month and year from request or default to current
        $currentMonth = (int) $request->input('month', now()->month);
        $currentYear = (int) $request->input('year', now()->year);

        // Validate month and year
        $currentMonth = max(1, min(12, $currentMonth));
        $currentYear = max(2000, min(2100, $currentYear));

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

        // Filter parameters
        $symbol = trim((string) $request->get('symbol', ''));
        $side = $request->get('side');
        $strategyId = $request->get('strategy_id');
        $timeframe = $request->get('timeframe');
        $hasChart = $request->boolean('has_chart');
        $hasAi = $request->boolean('has_ai');

        $filterHash = md5(json_encode([
            'symbol' => $symbol,
            'side' => $side,
            'strategy_id' => $strategyId,
            'timeframe' => $timeframe,
            'has_chart' => $hasChart,
            'has_ai' => $hasAi,
        ]));

        $version = Cache::get("trades_version_user_{$userId}", '1');
        $cacheKey = "pnl_calendar_u{$userId}_a{$accountMode}_m{$marketMode}_y{$currentYear}_m{$currentMonth}_f{$filterHash}_v{$version}";

        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($userId, $accountMode, $marketMode, $currentMonth, $currentYear, $symbol, $side, $strategyId, $timeframe, $hasChart, $hasAi) {
            // Fetch trades for the selected month with filters
            $tradeQuery = Trade::where('user_id', $userId)
                ->whereNotNull('close_datetime')
                ->whereYear('close_datetime', $currentYear)
                ->whereMonth('close_datetime', $currentMonth)
                ->select(['id', 'user_id', 'total_pnl', 'close_datetime', 'symbol', 'entry_side', 'strategy_id', 'timeframe', 'chart_picture', 'ai_analysis']);

            if ($accountMode !== 'all') {
                $tradeQuery->where('is_demo', $accountMode === 'demo');
            }
            if ($marketMode !== 'all') {
                $tradeQuery->where('market', $marketMode);
            }
            if (!empty($strategyId)) {
                $tradeQuery->where('strategy_id', $strategyId);
            }
            if (!empty($symbol)) {
                $tradeQuery->whereRaw('LOWER(symbol) LIKE ?', ['%'.strtolower($symbol).'%']);
            }
            if (!empty($side)) {
                $tradeQuery->where('entry_side', strtolower((string) $side));
            }
            if (!empty($timeframe)) {
                $tradeQuery->where('timeframe', $timeframe);
            }
            if ($hasChart) {
                $tradeQuery->whereNotNull('chart_picture')->where('chart_picture', '!=', '');
            }
            if ($hasAi) {
                $tradeQuery->whereNotNull('ai_analysis')->where('ai_analysis', '!=', '');
            }

            $trades = $tradeQuery->get();

            // Check if user has any trades at all for this user/mode
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
                        'pnl' => (float) $dayTrades->sum('total_pnl'),
                        'trades_count' => $dayTrades->count(),
                    ];
                });

            // Calculate summary stats
            $totalPnl = (float) $dailyPnl->sum('pnl');
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

        $strategies = Strategy::where('user_id', $userId)->orderBy('name')->get(['id', 'name']);

        return view('pnl-calendar.index', array_merge($data, compact(
            'strategies',
            'currentMonth',
            'currentYear',
            'prevMonth',
            'prevYear',
            'nextMonth',
            'nextYear',
            'symbol',
            'side',
            'strategyId',
            'timeframe',
            'hasChart',
            'hasAi'
        )));
    }
}
