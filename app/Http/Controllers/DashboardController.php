<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\MarketNews;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getDashboardData();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($data);
        }

        return view('dashboard.index', $data);
    }

    private function getDashboardData(): array
    {
        $userId = Auth::id();
        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');

        $latestNews = MarketNews::latest()->first();

        // Fetch Balances for Equity Curve (Pluck only what we need)
        $balanceQuery = Balance::where('user_id', $userId);
        if ($accountMode !== 'all') {
            $balanceQuery->where('is_demo', $accountMode === 'demo');
        }
        if ($marketMode !== 'all') {
            $balanceQuery->where('market', $marketMode);
        }

        $balances = $balanceQuery->oldest('date')->select(['date', 'total_equity'])->get();
        $equityCategories = $balances->map(fn ($b) => $b->date->format('M d, y'))->toArray();
        $equitySeries = $balances->map(fn ($b) => (float) $b->total_equity)->toArray();

        // Aggregate Trade Stats in SQL (Avoid loading all models)
        $tradeQuery = Trade::where('user_id', $userId)->whereNotNull('close_datetime');
        if ($accountMode !== 'all') {
            $tradeQuery->where('is_demo', $accountMode === 'demo');
        }
        if ($marketMode !== 'all') {
            $tradeQuery->where('market', $marketMode);
        }

        // Main Stats Aggregation
        $stats = (clone $tradeQuery)->selectRaw('
            COUNT(*) as trade_count,
            SUM(total_pnl) as total_pnl,
            COUNT(CASE WHEN total_pnl > 0 THEN 1 END) as win_count,
            SUM(CASE WHEN total_pnl > 0 THEN total_pnl ELSE 0 END) as total_win_amount,
            SUM(CASE WHEN total_pnl < 0 THEN ABS(total_pnl) ELSE 0 END) as total_loss_amount,
            MAX(total_pnl) as best_trade_pnl,
            MIN(total_pnl) as worst_trade_pnl
        ')->first();

        /** @var \App\Models\Trade $stats */
        $tradeCount = (int) ($stats->trade_count ?? 0);
        $winCount = (int) ($stats->win_count ?? 0);
        $totalPnl = (float) ($stats->total_pnl ?? 0);
        $totalWinAmount = (float) ($stats->total_win_amount ?? 0);
        $totalLossAmount = (float) ($stats->total_loss_amount ?? 0);

        $winRate = $tradeCount > 0 ? round(($winCount / $tradeCount) * 100, 1) : 0;
        $profitFactor = $totalLossAmount > 0 ? round($totalWinAmount / $totalLossAmount, 2) : ($totalWinAmount > 0 ? 99.99 : 0);
        $currentBalance = $balances->last() ? (float) $balances->last()->total_equity : 0;

        // Time-based PnL (Native SQL is faster)
        $now = now()->setTimezone('Asia/Manila');
        $todayPnl = (clone $tradeQuery)->where('close_datetime', '>=', $now->copy()->startOfDay()->setTimezone('UTC'))->sum('total_pnl');
        $weekPnl = (clone $tradeQuery)->where('close_datetime', '>=', $now->copy()->startOfWeek()->setTimezone('UTC'))->sum('total_pnl');
        $monthPnl = (clone $tradeQuery)->where('close_datetime', '>=', $now->copy()->startOfMonth()->setTimezone('UTC'))->sum('total_pnl');

        // Best/Worst Trade (Record)
        $bestTrade = (clone $tradeQuery)->orderByDesc('total_pnl')->first();
        $worstTrade = (clone $tradeQuery)->orderBy('total_pnl')->first();

        // Streaks (Still needs a loop, but we only pluck PnL values to keep it light)
        $pnlValues = (clone $tradeQuery)->orderBy('close_datetime', 'asc')->pluck('total_pnl')->toArray();
        $currentWinStreak = $maxWinStreak = $currentLossStreak = $maxLossStreak = 0;

        foreach ($pnlValues as $pnlValue) {
            $pnl = (float) $pnlValue;
            if ($pnl > 0) {
                $currentWinStreak++;
                $currentLossStreak = 0;
                $maxWinStreak = max($maxWinStreak, $currentWinStreak);
            } elseif ($pnl < 0) {
                $currentLossStreak++;
                $currentWinStreak = 0;
                $maxLossStreak = max($maxLossStreak, $currentLossStreak);
            } else {
                $currentWinStreak = $currentLossStreak = 0;
            }
        }

        // PnL Curve Data (Pluck date and PnL)
        $pnlChartData = (clone $tradeQuery)->orderBy('close_datetime', 'asc')
            ->select(['close_datetime', 'total_pnl'])
            ->get();

        $pnlCategories = [];
        $pnlSeries = [];
        $runningPnl = 0;

        /** @var \App\Models\Trade $t */
        foreach ($pnlChartData as $t) {
            $runningPnl += (float) $t->total_pnl;
            $closeTime = $t->close_datetime;
            $pnlCategories[] = $closeTime->setTimezone('Asia/Manila')->format('M d, y');
            $pnlSeries[] = round($runningPnl, 2);
        }

        $avgWin = $winCount > 0 ? $totalWinAmount / $winCount : 0;
        $strictLossCount = (int) (clone $tradeQuery)->where('total_pnl', '<', 0)->count();
        $avgLoss = $strictLossCount > 0 ? $totalLossAmount / $strictLossCount : 0;

        // Top Symbols
        $topSymbolsQuery = Trade::selectRaw('symbol, COUNT(*) as trades_count, SUM(total_pnl) as net_pnl, SUM(CASE WHEN total_pnl > 0 THEN 1 ELSE 0 END) as win_count')
            ->where('user_id', $userId)
            ->whereNotNull('close_datetime');

        if ($accountMode !== 'all') {
            $topSymbolsQuery->where('is_demo', $accountMode === 'demo');
        }
        if ($marketMode !== 'all') {
            $topSymbolsQuery->where('market', $marketMode);
        }

        $topSymbols = $topSymbolsQuery->groupBy('symbol')
            ->orderByDesc('trades_count')
            ->limit(5)
            ->get()
            ->map(function ($symbol) {
                $symbol->win_rate = $symbol->trades_count > 0 ? round(($symbol->win_count / $symbol->trades_count) * 100) : 0;
                $symbol->net_pnl = (float) $symbol->net_pnl;

                return $symbol;
            });

        // Recent Activity
        $recentActivityQuery = Trade::with(['strategy', 'reasons'])
            ->where('user_id', $userId)
            ->whereNotNull('close_datetime');

        if ($accountMode !== 'all') {
            $recentActivityQuery->where('is_demo', $accountMode === 'demo');
        }
        if ($marketMode !== 'all') {
            $recentActivityQuery->where('market', $marketMode);
        }

        $recentActivity = $recentActivityQuery->orderByDesc('close_datetime')
            ->limit(5)
            ->get()
            ->map(function ($trade) {
                $trade->human_time = \Carbon\Carbon::parse($trade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->diffForHumans();
                $trade->formatted_pnl = ($trade->total_pnl >= 0 ? '+' : '-').'$'.number_format(abs($trade->total_pnl), 2);

                return $trade;
            });

        return compact(
            'equityCategories',
            'equitySeries',
            'pnlCategories',
            'pnlSeries',
            'tradeCount',
            'winRate',
            'totalPnl',
            'currentBalance',
            'todayPnl',
            'weekPnl',
            'monthPnl',
            'profitFactor',
            'maxWinStreak',
            'maxLossStreak',
            'avgWin',
            'avgLoss',
            'bestTrade',
            'worstTrade',
            'topSymbols',
            'recentActivity',
            'latestNews'
        );
    }
}
