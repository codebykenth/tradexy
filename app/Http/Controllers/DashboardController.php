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

        // 1. Fetch Balances for Equity Curve
        $balanceQuery = Balance::where('user_id', $userId);
        if ($accountMode !== 'all') {
            $balanceQuery->where('is_demo', $accountMode === 'demo');
        }
        if ($marketMode !== 'all') {
            $balanceQuery->where('market', $marketMode);
        }
        $balances = $balanceQuery->orderBy('date', 'asc')->get();

        $equityCategories = $balances->pluck('date')->map(fn ($date) => $date->format('M d, y'))->toArray();
        $equitySeries = $balances->pluck('total_equity')->map(fn ($val) => (float) $val)->toArray();

        // 2. Fetch Trades for PnL Curve and Overall Stats
        $tradeQuery = Trade::where('user_id', $userId)->whereNotNull('close_datetime');
        if ($accountMode !== 'all') {
            $tradeQuery->where('is_demo', $accountMode === 'demo');
        }
        if ($marketMode !== 'all') {
            $tradeQuery->where('market', $marketMode);
        }
        $trades = $tradeQuery->orderBy('close_datetime', 'asc')->get();

        $pnlCategories = [];
        $pnlSeries = [];
        $cumulativePnl = 0;

        $winCount = 0;
        $totalPnl = 0;
        $totalWinAmount = 0;
        $totalLossAmount = 0;

        // Time-based PnL Tracking
        $todayPnl = 0;
        $weekPnl = 0;
        $monthPnl = 0;

        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();

        foreach ($trades as $trade) {
            $pnl = (float) $trade->total_pnl;
            $cumulativePnl += $pnl;
            $totalPnl += $pnl;

            // X-Axis formatting — convert UTC to Manila for display
            $pnlCategories[] = \Carbon\Carbon::parse($trade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, y');
            $pnlSeries[] = round($cumulativePnl, 2);

            if ($pnl > 0) {
                $winCount++;
                $totalWinAmount += $pnl;
            } else {
                $totalLossAmount += abs($pnl);
            }

            // Time aggregations — parse as UTC, convert to Manila for comparison
            $closeTime = \Carbon\Carbon::parse($trade->close_datetime, 'UTC')->setTimezone('Asia/Manila');
            if ($closeTime->greaterThanOrEqualTo($startOfDay)) {
                $todayPnl += $pnl;
            }
            if ($closeTime->greaterThanOrEqualTo($startOfWeek)) {
                $weekPnl += $pnl;
            }
            if ($closeTime->greaterThanOrEqualTo($startOfMonth)) {
                $monthPnl += $pnl;
            }
        }

        // 3. Stats metrics
        $tradeCount = $trades->count();
        $winRate = $tradeCount > 0 ? round(($winCount / $tradeCount) * 100, 1) : 0;
        $currentBalance = $balances->last() ? (float) $balances->last()->total_equity : 0;

        // Profit Factor
        $profitFactor = $totalLossAmount > 0 ? round($totalWinAmount / $totalLossAmount, 2) : ($totalWinAmount > 0 ? 99.99 : 0);

        // Streaks & Averages
        $currentWinStreak = 0;
        $maxWinStreak = 0;
        $currentLossStreak = 0;
        $maxLossStreak = 0;

        foreach ($trades as $trade) {
            $pnl = (float) $trade->total_pnl;
            if ($pnl > 0) {
                $currentWinStreak++;
                $currentLossStreak = 0;
                if ($currentWinStreak > $maxWinStreak) {
                    $maxWinStreak = $currentWinStreak;
                }
            } elseif ($pnl < 0) {
                $currentLossStreak++;
                $currentWinStreak = 0;
                if ($currentLossStreak > $maxLossStreak) {
                    $maxLossStreak = $currentLossStreak;
                }
            } else {
                // Break both streaks on a break-even trade
                $currentWinStreak = 0;
                $currentLossStreak = 0;
            }
        }

        $avgWin = $winCount > 0 ? $totalWinAmount / $winCount : 0;
        $lossCount = $tradeCount - $winCount; // Approximation (excluding breakeven)
        // Recalculate strict loss count for accurate average
        $strictLossCount = $trades->where('total_pnl', '<', 0)->count();
        $avgLoss = $strictLossCount > 0 ? $totalLossAmount / $strictLossCount : 0;

        // Best and Worst Trade
        $bestTrade = $trades->sortByDesc('total_pnl')->first();
        $worstTrade = $trades->sortBy('total_pnl')->first();

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
