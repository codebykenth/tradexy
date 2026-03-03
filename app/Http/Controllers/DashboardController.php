<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Fetch Balances for Equity Curve
        $balances = Balance::where('user_id', $userId)
            ->orderBy('date', 'asc')
            ->get();

        $equityCategories = $balances->pluck('date')->map(fn($date) => $date->format('M d, y'))->toArray();
        $equitySeries = $balances->pluck('total_equity')->toArray();

        // 2. Fetch Trades for PnL Curve and Overall Stats
        $trades = Trade::where('user_id', $userId)
            ->whereNotNull('close_datetime')
            ->orderBy('close_datetime', 'asc')
            ->get();

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

            // X-Axis formatting (Date with year)
            $pnlCategories[] = \Carbon\Carbon::parse($trade->close_datetime)->format('M d, y');
            $pnlSeries[] = round($cumulativePnl, 2);

            if ($pnl > 0) {
                $winCount++;
                $totalWinAmount += $pnl;
            } else {
                $totalLossAmount += abs($pnl);
            }

            // Time aggregations
            $closeTime = \Carbon\Carbon::parse($trade->close_datetime);
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
        $currentBalance = $balances->last() ? $balances->last()->total_equity : 0;

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
        $topSymbols = Trade::selectRaw('symbol, COUNT(*) as trades_count, SUM(total_pnl) as net_pnl, SUM(CASE WHEN total_pnl > 0 THEN 1 ELSE 0 END) as win_count')
            ->where('user_id', $userId)
            ->whereNotNull('close_datetime')
            ->groupBy('symbol')
            ->orderByDesc('trades_count')
            ->limit(5)
            ->get()
            ->map(function ($symbol) {
                $symbol->win_rate = $symbol->trades_count > 0 ? round(($symbol->win_count / $symbol->trades_count) * 100) : 0;
                return $symbol;
            });

        // Recent Activity
        $recentActivity = Trade::where('user_id', $userId)
            ->whereNotNull('close_datetime')
            ->orderByDesc('close_datetime')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
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
            'recentActivity'
        ));
    }
}
