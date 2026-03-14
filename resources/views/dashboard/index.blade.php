<x-layouts.app title="Dashboard - Tradexy">
    <div class="max-w-7xl mx-auto px-6 space-y-6 mb-8 mt-6">

        <!-- Welcome & Time -->
        <x-page-title title="Dashboard"
            subtitle="Welcome back, {{ Auth::user()->name }} • {{ now()->format('l, F d, Y') }}" />

        @if($tradeCount > 0)
            <!-- Top Summary Cards Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                <!-- Today -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Today</span>
                    <span class="text-xl font-bold {{ $todayPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $todayPnl >= 0 ? '+' : '-' }}${{ number_format(abs($todayPnl), 2) }}
                    </span>
                </div>
                <!-- This Week -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">This
                        Week</span>
                    <span class="text-xl font-bold {{ $weekPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $weekPnl >= 0 ? '+' : '-' }}${{ number_format(abs($weekPnl), 2) }}
                    </span>
                </div>
                <!-- This Month -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">This
                        Month</span>
                    <span class="text-xl font-bold {{ $monthPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $monthPnl >= 0 ? '+' : '-' }}${{ number_format(abs($monthPnl), 2) }}
                    </span>
                </div>
                <!-- All Time -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">All
                        Time</span>
                    <span class="text-xl font-bold {{ $totalPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $totalPnl >= 0 ? '+' : '-' }}${{ number_format(abs($totalPnl), 2) }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $tradeCount }} trades</span>
                </div>

                <!-- Win Rate -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Win
                        Rate</span>
                    <span class="text-xl font-bold {{ $winRate >= 50 ? 'text-blue-500' : 'text-red-500' }}">
                        {{ number_format($winRate, 1) }}%
                    </span>
                </div>

                <!-- Profit Factor -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">P/F</span>
                    <span
                        class="text-xl font-bold {{ $profitFactor >= 1.5 ? 'text-green-500' : ($profitFactor >= 1 ? 'text-blue-500' : 'text-red-500') }}">
                        {{ number_format($profitFactor, 2) }}
                    </span>
                </div>
            </div>

            <!-- Charts Grid (Equity vs PnL side-by-side or stacked on mobile) -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
                <!-- Equity Curve -->
                <div class="relative min-h-[350px]">
                    <x-charts.area id="equityChart" title="Equity Curve" :series="$equitySeries"
                        :categories="$equityCategories" color="#6366f1" />
                </div>

                <!-- PnL Curve -->
                <div class="relative min-h-[350px]">
                    <x-charts.area id="pnlChart" title="Cumulative PnL" :series="$pnlSeries" :categories="$pnlCategories"
                        color="{{ $totalPnl >= 0 ? '#10B981' : '#EF4444' }}" />
                </div>
            </div>

            @if($latestNews)
                <!-- Market Insights Snippet -->
                <div class="bg-gradient-to-br from-primary/5 via-base-100 to-base-100 dark:from-primary/10 dark:via-[#1A1C23] dark:to-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 mb-6 shadow-sm overflow-hidden relative">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-primary/5 rounded-full blur-3xl"></div>
                    
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
                        <div class="space-y-1">
                            <h3 class="text-sm font-black uppercase tracking-[0.2em] text-primary/60">Macro Intel</h3>
                            <h2 class="text-2xl font-black italic tracking-tighter">AI Market <span class="text-primary">Insights</span></h2>
                            <p class="text-xs font-medium opacity-50 uppercase tracking-widest">{{ $latestNews->date_range }}</p>
                        </div>
                        
                        <div class="flex flex-wrap gap-4 items-center">
                            @if(isset($latestNews->ai_analysis['gold']))
                                @php $gold = $latestNews->ai_analysis['gold']; @endphp
                                <div class="flex items-center gap-3 bg-white dark:bg-base-200 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                                    <div class="w-8 h-8 rounded-lg bg-[#d4af37] flex items-center justify-center text-white shadow-lg shadow-[#d4af37]/20 text-[10px] font-black">AU</div>
                                    <div>
                                        <p class="text-[8px] font-black uppercase opacity-40 leading-none mb-1">Gold Bias</p>
                                        <p @class([
                                            'text-xs font-black uppercase tracking-wider leading-none',
                                            'text-green-500' => ($gold['bias'] ?? '') === 'Bullish',
                                            'text-red-500' => ($gold['bias'] ?? '') === 'Bearish',
                                        ])>{{ $gold['bias'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(isset($latestNews->ai_analysis['crypto']))
                                @php $crypto = $latestNews->ai_analysis['crypto']; @endphp
                                <div class="flex items-center gap-3 bg-white dark:bg-base-200 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                                    <div class="w-8 h-8 rounded-lg bg-[#f7931a] flex items-center justify-center text-white shadow-lg shadow-[#f7931a]/20 text-[10px] font-black">BTC</div>
                                    <div>
                                        <p class="text-[8px] font-black uppercase opacity-40 leading-none mb-1">BTC Bias</p>
                                        <p @class([
                                            'text-xs font-black uppercase tracking-wider leading-none',
                                            'text-green-500' => ($crypto['bias'] ?? '') === 'Bullish',
                                            'text-red-500' => ($crypto['bias'] ?? '') === 'Bearish',
                                        ])>{{ $crypto['bias'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @endif

                            <a href="{{ route('daily-news.show', $latestNews->id) }}" class="btn btn-primary btn-sm rounded-xl px-5 gap-2 group">
                                View Brief
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Bottom Widgets Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Performance Insights Widget -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-5 flex flex-col h-full">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Performance Insights</h3>

                    <!-- Best Trade -->
                    <div
                        class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 rounded-lg p-3 mb-3">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 uppercase">🏆 Best
                                Trade</span>
                        </div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $bestTrade?->symbol ?? 'N/A' }}
                        </div>
                        <div class="text-emerald-600 dark:text-emerald-400 font-bold text-xl">
                            +${{ number_format($bestTrade?->total_pnl ?? 0, 2) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $bestTrade ? \Carbon\Carbon::parse($bestTrade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y') : '' }}
                        </div>
                    </div>

                    <!-- Worst Trade -->
                    <div
                        class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/30 rounded-lg p-3 mb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-red-600 dark:text-red-400 uppercase">📉 Worst
                                Trade</span>
                        </div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $worstTrade?->symbol ?? 'N/A' }}
                        </div>
                        <div class="text-red-600 dark:text-red-400 font-bold text-xl">
                            -${{ number_format(abs($worstTrade?->total_pnl ?? 0), 2) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $worstTrade ? \Carbon\Carbon::parse($worstTrade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y') : '' }}
                        </div>
                    </div>

                    <!-- Streaks and Averages Grid -->
                    <div class="grid grid-cols-2 gap-3 mt-auto">
                        <!-- Max Win Streak -->
                        <div
                            class="bg-gray-50 dark:bg-[#20222a] border border-gray-200 dark:border-gray-800 rounded-lg p-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Max Win Streak</div>
                            <div class="text-lg font-bold text-green-600 dark:text-green-500 flex items-center gap-1">
                                {{ $maxWinStreak }} 🔥
                            </div>
                        </div>

                        <!-- Max Loss Streak -->
                        <div
                            class="bg-gray-50 dark:bg-[#20222a] border border-gray-200 dark:border-gray-800 rounded-lg p-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Max Loss Streak</div>
                            <div class="text-lg font-bold text-red-600 dark:text-red-500 flex items-center gap-1">
                                {{ $maxLossStreak }} ⚠️
                            </div>
                        </div>

                        <!-- Avg Win -->
                        <div
                            class="bg-gray-50 dark:bg-[#20222a] border border-gray-200 dark:border-gray-800 rounded-lg p-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Avg Win</div>
                            <div class="text-lg font-bold text-green-600 dark:text-green-500">
                                +${{ number_format($avgWin, 2) }}
                            </div>
                        </div>

                        <!-- Avg Loss -->
                        <div
                            class="bg-gray-50 dark:bg-[#20222a] border border-gray-200 dark:border-gray-800 rounded-lg p-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Avg Loss</div>
                            <div class="text-lg font-bold text-red-600 dark:text-red-500">
                                -${{ number_format(abs($avgLoss), 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Symbols -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-5 flex flex-col h-full">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Top Symbols</h3>
                    <div class="space-y-4">
                        @forelse($topSymbols as $symbolStat)
                            <div
                                class="flex justify-between items-center pb-2 border-b border-gray-100 dark:border-gray-800 last:border-0 last:pb-0">
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $symbolStat->symbol }}</div>
                                    <div class="text-xs text-gray-500">{{ $symbolStat->trades_count }} trades &bull;
                                        {{ $symbolStat->win_rate }}% WR
                                    </div>
                                </div>
                                <div class="font-bold {{ $symbolStat->net_pnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $symbolStat->net_pnl >= 0 ? '+' : '-' }}${{ number_format(abs($symbolStat->net_pnl), 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No symbol data available.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Activity -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-5 flex flex-col h-full">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
                    <div class="space-y-3">
                        @forelse($recentActivity as $trade)
                            <div
                                class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-800  last:border-0 last:pb-0">
                                <!-- Circular icon for W/L -->
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold font-mono shrink-0 {{ $trade->total_pnl >= 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-500' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-500' }}">
                                    {{ $trade->total_pnl >= 0 ? 'W' : 'L' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="font-bold text-gray-900 dark:text-white truncate">{{ $trade->symbol }}</span>
                                        <span
                                            class="text-[10px] px-1.5 py-0.5 rounded {{ strtolower($trade->entry_side) === 'long' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400' }}">
                                            {{ strtoupper($trade->entry_side) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <span
                                            class="{{ $trade->total_pnl >= 0 ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                                            {{ $trade->total_pnl >= 0 ? '+' : '-' }}${{ number_format(abs($trade->total_pnl), 2) }}
                                        </span>
                                        &bull;
                                        {{ \Carbon\Carbon::parse($trade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No recent activity.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <div class="mt-8">
                <div
                    class="bg-gray-50 dark:bg-[#141414] border-2 border-dashed border-gray-300 dark:border-[#1F1F1E] rounded-xl p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>

                    <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-2">No trading data available</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Fetch your balances and trades to generate your
                        overview curves.</p>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Listen for real-time trade updates and refresh the dashboard
        if (window.Echo) {
            window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
                .listen('.NewTradesFetched', (e) => {
                    // Wait 2 seconds so the user can see the toast message before reloading
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                });
        }
    </script>
</x-layouts.app>