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
                    <span id="stat-today" class="text-xl font-bold {{ $todayPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $todayPnl >= 0 ? '+' : '-' }}{{ $currencySymbol }}{{ number_format(abs($todayPnl), 2) }}
                    </span>
                </div>
                <!-- This Week -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">This
                        Week</span>
                    <span id="stat-week" class="text-xl font-bold {{ $weekPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $weekPnl >= 0 ? '+' : '-' }}{{ $currencySymbol }}{{ number_format(abs($weekPnl), 2) }}
                    </span>
                </div>
                <!-- This Month -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">This
                        Month</span>
                    <span id="stat-month" class="text-xl font-bold {{ $monthPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $monthPnl >= 0 ? '+' : '-' }}{{ $currencySymbol }}{{ number_format(abs($monthPnl), 2) }}
                    </span>
                </div>
                <!-- All Time -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">All
                        Time</span>
                    <span id="stat-total" class="text-xl font-bold {{ $totalPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $totalPnl >= 0 ? '+' : '-' }}{{ $currencySymbol }}{{ number_format(abs($totalPnl), 2) }}
                    </span>
                    <span id="stat-count" class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $tradeCount }} trades</span>
                </div>

                <!-- Win Rate -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Win
                        Rate</span>
                    <span id="stat-winrate" class="text-xl font-bold {{ $winRate >= 50 ? 'text-blue-500' : 'text-red-500' }}">
                        {{ number_format($winRate, 1) }}%
                    </span>
                </div>

                <!-- Profit Factor -->
                <div
                    class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex flex-col justify-center">
                    <span
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">P/F</span>
                    <span id="stat-profitfactor"
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
                        :categories="$equityCategories" color="#6366f1" :prefix="$currencySymbol" />
                </div>

                <!-- PnL Curve -->
                <div class="relative min-h-[350px]">
                    <x-charts.area id="pnlChart" title="Cumulative PnL" :series="$pnlSeries" :categories="$pnlCategories"
                        color="{{ $totalPnl >= 0 ? '#10B981' : '#EF4444' }}" :prefix="$currencySymbol" />
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
                                    <div class="w-8 h-8 rounded-lg bg-[#d4af37] flex items-center justify-center text-white shadow-lg shadow-[#d4af37]/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M21.5,13.5L19,6H5L2.5,13.5L3.5,18H20.5L21.5,13.5M16.5,14H7.5V12H16.5V14Z"/>
                                        </svg>
                                    </div>
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
                                    <div class="w-8 h-8 rounded-lg bg-[#f7931a] flex items-center justify-center text-white shadow-lg shadow-[#f7931a]/20">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512">
                                            <path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zm-141.651-35.33c4.935-32.928-20.154-50.596-54.397-62.396l11.119-44.544-27.108-6.758-10.825 43.412c-7.12-1.778-14.453-3.443-21.722-5.105l10.893-43.682-27.114-6.764-11.119 44.57c-5.896-1.342-11.666-2.673-17.301-4.066l.014-.064-37.4 9.333 7.211 28.914s20.122-4.612 19.704-4.25c10.987 2.743 14.633 10.016 14.262 15.79l-14.28 57.262c.858.214 1.973.524 3.193.847l-3.213-.803-20.015 80.24c-1.347 3.328-4.743 8.322-12.433 6.406.273.392-19.71-4.918-19.71-4.918l-13.456 31.027 35.29 8.805c6.563 1.64 13.012 3.341 19.346 4.965l-11.238 45.066 27.108 6.761 11.119-44.574c7.391 2.01 14.567 3.899 21.572 5.698l-11.096 44.507 27.114 6.764 11.241-45.093c46.221 8.749 80.958 5.222 95.59-36.567 11.79-33.662-1.126-53.059-25.46-65.719 17.722-4.09 31.065-15.751 34.621-39.757zm-62.152 86.842c-8.386 33.651-65.138 15.46-83.501 10.891l14.898-59.72c18.359 4.567 77.108 13.628 68.603 48.829zm10.354-87.31c-7.644 30.663-54.908 15.111-70.211 11.291l13.528-54.218c15.303 3.821 64.429 10.954 56.683 42.927z"/>
                                        </svg>
                                    </div>
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
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 uppercase flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-emerald-500">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                </svg>
                                Best Trade
                            </span>
                        </div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $bestTrade?->symbol ?? 'N/A' }}
                        </div>
                        <div class="text-emerald-600 dark:text-emerald-400 font-bold text-xl">
                            +{{ $currencySymbol }}{{ number_format(\App\Helpers\CurrencyFormatter::normalizeValueAmount($bestTrade?->total_pnl ?? 0, $bestTrade?->market ?? 'crypto'), 2) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $bestTrade ? \Carbon\Carbon::parse($bestTrade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y') : '' }}
                        </div>
                    </div>

                    <!-- Worst Trade -->
                    <div
                        class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/30 rounded-lg p-3 mb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-red-600 dark:text-red-400 uppercase flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-red-500">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.51m-3.182 5.51-5.511-3.181" />
                                </svg>
                                Worst Trade
                            </span>
                        </div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $worstTrade?->symbol ?? 'N/A' }}
                        </div>
                        <div class="text-red-600 dark:text-red-400 font-bold text-xl">
                            -{{ $currencySymbol }}{{ number_format(abs(\App\Helpers\CurrencyFormatter::normalizeValueAmount($worstTrade?->total_pnl ?? 0, $worstTrade?->market ?? 'crypto')), 2) }}
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
                                {{ $maxWinStreak }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-orange-500">
                                  <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.176 7.547 7.547 0 0 1-1.705-1.715.75.75 0 0 0-1.152-.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 0 1 1.925-3.546 3.75 3.75 0 0 1 3.255 3.718Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <!-- Max Loss Streak -->
                        <div
                            class="bg-gray-50 dark:bg-[#20222a] border border-gray-200 dark:border-gray-800 rounded-lg p-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Max Loss Streak</div>
                            <div class="text-lg font-bold text-red-600 dark:text-red-500 flex items-center gap-1">
                                {{ $maxLossStreak }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-yellow-500">
                                  <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <!-- Avg Win -->
                        <div
                            class="bg-gray-50 dark:bg-[#20222a] border border-gray-200 dark:border-gray-800 rounded-lg p-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Avg Win</div>
                            <div class="text-lg font-bold text-green-600 dark:text-green-500">
                                +{{ $currencySymbol }}{{ number_format($avgWin, 2) }}
                            </div>
                        </div>

                        <!-- Avg Loss -->
                        <div
                            class="bg-gray-50 dark:bg-[#20222a] border border-gray-200 dark:border-gray-800 rounded-lg p-3">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Avg Loss</div>
                            <div class="text-lg font-bold text-red-600 dark:text-red-500">
                                -{{ $currencySymbol }}{{ number_format(abs($avgLoss), 2) }}
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
                                    {{ $symbolStat->net_pnl >= 0 ? '+' : '-' }}{{ $currencySymbol }}{{ number_format(abs($symbolStat->net_pnl), 2) }}
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
                    <div id="recent-activity-list" class="space-y-3">
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
                                            {{ $trade->total_pnl >= 0 ? '+' : '-' }}{{ $currencySymbol }}{{ number_format(abs(\App\Helpers\CurrencyFormatter::normalizeValueAmount($trade->total_pnl, $trade->market)), 2) }}
                                        </span>
                                        &bull;
                                        {{ $trade->human_time }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No recent activity.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <script>
                if (window.Echo) {
                    window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
                        .listen('.NewTradesFetched', (e) => {
                            console.log('Real-time dashboard update:', e);
                            if (window.showToast) {
                                window.showToast(e.message || 'Data updated!', 'success');
                            }
                            refreshDashboard();
                        });
                }

                async function refreshDashboard() {
                    try {
                        const response = await fetch("{{ url('dashboard') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        // 1. Update Stats
                        updateStat('stat-today', data.todayPnl);
                        updateStat('stat-week', data.weekPnl);
                        updateStat('stat-month', data.monthPnl);
                        updateStat('stat-total', data.totalPnl);
                        document.getElementById('stat-count').textContent = `${data.tradeCount} trades`;
                        
                        document.getElementById('stat-winrate').textContent = `${data.winRate}%`;
                        document.getElementById('stat-winrate').className = `text-xl font-bold ${data.winRate >= 50 ? 'text-blue-500' : 'text-red-500'}`;
                        
                        document.getElementById('stat-profitfactor').textContent = data.profitFactor.toFixed(2);
                        document.getElementById('stat-profitfactor').className = `text-xl font-bold ${data.profitFactor >= 1.5 ? 'text-green-500' : (data.profitFactor >= 1 ? 'text-blue-500' : 'text-red-500')}`;

                        // 2. Update Charts
                        if (window.ApexCharts) {
                            ApexCharts.exec('equityChart', 'updateOptions', {
                                series: [{ data: data.equitySeries }],
                                xaxis: { categories: data.equityCategories }
                            });
                            ApexCharts.exec('pnlChart', 'updateOptions', {
                                series: [{ data: data.pnlSeries }],
                                xaxis: { categories: data.pnlCategories },
                                colors: [data.totalPnl >= 0 ? '#10B981' : '#EF4444']
                            });
                        }

                        // 3. Update Recent Activity
                        const list = document.getElementById('recent-activity-list');
                        if (list && data.recentActivity) {
                            list.innerHTML = data.recentActivity.map(trade => `
                                <div class="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-gray-800 last:border-0 last:pb-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold font-mono shrink-0 ${trade.total_pnl >= 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-500' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-500'}">
                                        ${trade.total_pnl >= 0 ? 'W' : 'L'}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 dark:text-white truncate">${trade.symbol}</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded ${trade.entry_side.toLowerCase() === 'long' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400'}">
                                                ${trade.entry_side.toUpperCase()}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            <span class="${trade.total_pnl >= 0 ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400'}">
                                                ${trade.formatted_pnl}
                                            </span>
                                            &bull; ${trade.human_time}
                                        </div>
                                    </div>
                                </div>
                            `).join('');
                        }
                    } catch (error) {
                        console.error('Error refreshing dashboard:', error);
                    }
                }

                function updateStat(id, value) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    const formatted = (value >= 0 ? '+' : '-') + data.currencySymbol + Math.abs(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    el.textContent = formatted;
                    el.className = `text-xl font-bold ${value >= 0 ? 'text-green-500' : 'text-red-500'}`;
                }
            </script>
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
</x-layouts.app>