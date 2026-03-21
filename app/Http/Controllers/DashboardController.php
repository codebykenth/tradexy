<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\CurrencyFormatter;
use App\Models\Balance;
use App\Models\MarketNews;
use App\Models\Trade;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly CurrencyService $currencyService
    ) {}

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
        $prefCurrency = session('preferred_currency', 'USD');

        $version = Cache::get("trades_version_user_{$userId}", '1');
        $cacheKey = "dash_v{$version}_u{$userId}_m{$accountMode}_mar{$marketMode}_cur{$prefCurrency}";

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($userId, $accountMode, $marketMode, $prefCurrency) {
            $latestNews = MarketNews::latest()->first();
            $rate = $this->currencyService->getRate();

            // Fetch Balances for Equity Curve
            $balanceQuery = Balance::where('user_id', $userId);
            if ($accountMode !== 'all') {
                $balanceQuery->where('is_demo', $accountMode === 'demo');
            }
            if ($marketMode !== 'all') {
                $balanceQuery->where('market', $marketMode);
            }

            $balances = $balanceQuery->oldest('date')->select(['date', 'total_equity', 'market'])->get();

            // Normalize Equity Curve
            $equityData = $balances->groupBy(fn ($b) => $b->date->format('Y-m-d'))
                ->map(function ($group) use ($rate, $prefCurrency) {
                    $total = 0;
                    foreach ($group as $b) {
                        if ($b->market === 'crypto') {
                            $total += ($prefCurrency === 'PHP') ? ((float) $b->total_equity * $rate) : (float) $b->total_equity;
                        } else {
                            $total += ($prefCurrency === 'USD') ? ((float) $b->total_equity / $rate) : (float) $b->total_equity;
                        }
                    }

                    return [
                        'date' => $group->first()->date->format('M d, y'),
                        'value' => round($total, 2),
                    ];
                });

            $equityCategories = $equityData->pluck('date')->toArray();
            $equitySeries = $equityData->pluck('value')->toArray();

            // Separate Market Stats Aggregation
            $tradeQuery = Trade::where('user_id', $userId)->whereNotNull('close_datetime');
            if ($accountMode !== 'all') {
                $tradeQuery->where('is_demo', $accountMode === 'demo');
            }
            if ($marketMode !== 'all') {
                $tradeQuery->where('market', $marketMode);
            }

            $stats = (clone $tradeQuery)->selectRaw("
                COUNT(*) as trade_count,
                SUM(CASE WHEN market = 'crypto' THEN total_pnl ELSE 0 END) as crypto_pnl,
                SUM(CASE WHEN market = 'pse' THEN total_pnl ELSE 0 END) as pse_pnl,
                COUNT(CASE WHEN total_pnl > 0 THEN 1 END) as win_count,
                SUM(CASE WHEN market = 'crypto' AND total_pnl > 0 THEN total_pnl ELSE 0 END) as crypto_win,
                SUM(CASE WHEN market = 'pse' AND total_pnl > 0 THEN total_pnl ELSE 0 END) as pse_win,
                SUM(CASE WHEN market = 'crypto' AND total_pnl < 0 THEN ABS(total_pnl) ELSE 0 END) as crypto_loss,
                SUM(CASE WHEN market = 'pse' AND total_pnl < 0 THEN ABS(total_pnl) ELSE 0 END) as pse_loss
            ")->first();

            /** @var \App\Models\Trade $stats */
            $tradeCount = (int) ($stats->trade_count ?? 0);
            $winCount = (int) ($stats->win_count ?? 0);

            // Normalize PnL Stat
            $totalPnl = ($prefCurrency === 'PHP')
                ? ($stats->crypto_pnl * $rate) + $stats->pse_pnl
                : $stats->crypto_pnl + ($stats->pse_pnl / $rate);

            $totalWinAmount = ($prefCurrency === 'PHP')
                ? ($stats->crypto_win * $rate) + $stats->pse_win
                : $stats->crypto_win + ($stats->pse_win / $rate);

            $totalLossAmount = ($prefCurrency === 'PHP')
                ? ($stats->crypto_loss * $rate) + $stats->pse_loss
                : $stats->crypto_loss + ($stats->pse_loss / $rate);

            $winRate = $tradeCount > 0 ? round(($winCount / $tradeCount) * 100, 1) : 0;
            $profitFactor = $totalLossAmount > 0 ? round($totalWinAmount / $totalLossAmount, 2) : ($totalWinAmount > 0 ? 99.99 : 0);

            // Current Balance requires market consideration on the last balance entry
            $lastBalance = $balances->last();
            if ($lastBalance) {
                if ($marketMode === 'all') {
                    $currentBalance = $equitySeries[array_key_last($equitySeries)] ?? 0;
                } else {
                    if ($lastBalance->market === 'crypto') {
                        $currentBalance = ($prefCurrency === 'PHP') ? ($lastBalance->total_equity * $rate) : (float) $lastBalance->total_equity;
                    } else {
                        $currentBalance = ($prefCurrency === 'USD') ? ($lastBalance->total_equity / $rate) : (float) $lastBalance->total_equity;
                    }
                }
            } else {
                $currentBalance = 0;
            }

            // Time-based PnL
            $now = now()->setTimezone('Asia/Manila');
            $periodicTrades = (clone $tradeQuery)->select(['market', 'total_pnl', 'close_datetime'])->get();

            $todayPnl = $weekPnl = $monthPnl = 0;
            $startOfDay = $now->copy()->startOfDay()->setTimezone('UTC');
            $startOfWeek = $now->copy()->startOfWeek()->setTimezone('UTC');
            $startOfMonth = $now->copy()->startOfMonth()->setTimezone('UTC');

            foreach ($periodicTrades as $t) {
                $val = ($t->market === 'crypto')
                    ? (($prefCurrency === 'PHP') ? ($t->total_pnl * $rate) : $t->total_pnl)
                    : (($prefCurrency === 'USD') ? ($t->total_pnl / $rate) : $t->total_pnl);

                if ($t->close_datetime >= $startOfDay) {
                    $todayPnl += $val;
                }
                if ($t->close_datetime >= $startOfWeek) {
                    $weekPnl += $val;
                }
                if ($t->close_datetime >= $startOfMonth) {
                    $monthPnl += $val;
                }
            }

            // Best/Worst Trade (Find in Normalized Value)
            $allTradesForRecords = (clone $tradeQuery)->get();
            $bestTrade = $worstTrade = null;
            $maxVal = -INF;
            $minVal = INF;

            foreach ($allTradesForRecords as $t) {
                $val = ($t->market === 'crypto')
                    ? (($prefCurrency === 'PHP') ? ($t->total_pnl * $rate) : (float) $t->total_pnl)
                    : (($prefCurrency === 'USD') ? ($t->total_pnl / $rate) : (float) $t->total_pnl);

                if ($val > $maxVal) {
                    $maxVal = $val;
                    $bestTrade = $t;
                }
                if ($val < $minVal) {
                    $minVal = $val;
                    $worstTrade = $t;
                }
            }

            // Streaks
            $streakPnls = (clone $tradeQuery)->orderBy('close_datetime', 'asc')->pluck('total_pnl')->toArray();
            $currentWinStreak = $maxWinStreak = $currentLossStreak = $maxLossStreak = 0;
            foreach ($streakPnls as $pnl) {
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

            // PnL Curve Data (Unified running total in target currency)
            $pnlChartData = (clone $tradeQuery)->orderBy('close_datetime', 'asc')->select(['close_datetime', 'total_pnl', 'market'])->get();
            $pnlCategories = [];
            $pnlSeries = [];
            $runningPnl = 0;

            foreach ($pnlChartData as $t) {
                $val = ($t->market === 'crypto')
                    ? (($prefCurrency === 'PHP') ? ($t->total_pnl * $rate) : (float) $t->total_pnl)
                    : (($prefCurrency === 'USD') ? ($t->total_pnl / $rate) : (float) $t->total_pnl);

                $runningPnl += $val;
                $pnlCategories[] = $t->close_datetime->setTimezone('Asia/Manila')->format('M d, y');
                $pnlSeries[] = round($runningPnl, 2);
            }

            $avgWin = $winCount > 0 ? $totalWinAmount / $winCount : 0;
            $strictLossCount = $tradeCount - $winCount; // Approximation
            $avgLoss = $strictLossCount > 0 ? $totalLossAmount / $strictLossCount : 0;

            // Top Symbols (Needs manual normalization per row)
            $topSymbols = Trade::selectRaw('symbol, market, COUNT(*) as trades_count, SUM(total_pnl) as net_pnl, SUM(CASE WHEN total_pnl > 0 THEN 1 ELSE 0 END) as win_count')
                ->where('user_id', $userId)
                ->whereNotNull('close_datetime')
                ->when($accountMode !== 'all', fn ($q) => $q->where('is_demo', $accountMode === 'demo'))
                ->when($marketMode !== 'all', fn ($q) => $q->where('market', $marketMode))
                ->groupBy('symbol', 'market')
                ->orderByDesc('trades_count')
                ->limit(5)
                ->get()
                ->map(function ($symbol) use ($rate, $prefCurrency) {
                    $symbol->win_rate = $symbol->trades_count > 0 ? round(($symbol->win_count / $symbol->trades_count) * 100) : 0;
                    $val = ($symbol->market === 'crypto')
                        ? (($prefCurrency === 'PHP') ? ($symbol->net_pnl * $rate) : (float) $symbol->net_pnl)
                        : (($prefCurrency === 'USD') ? ($symbol->net_pnl / $rate) : (float) $symbol->net_pnl);
                    $symbol->net_pnl = (float) $val;

                    return $symbol;
                });

            // Recent Activity
            $recentActivity = (clone $tradeQuery)->with(['strategy', 'reasons'])->orderByDesc('close_datetime')->limit(5)->get()
                ->map(function ($trade) {
                    $trade->human_time = $trade->close_datetime->setTimezone('Asia/Manila')->diffForHumans();
                    $trade->formatted_pnl = CurrencyFormatter::format($trade->total_pnl, $trade->market);

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
            ) + ['currencySymbol' => ($prefCurrency === 'PHP' ? '₱' : '$')];
        });
    }
}
