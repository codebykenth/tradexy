<x-layouts.app>
    <div class="max-w-7xl mx-auto px-6 space-y-6 mb-8 mt-6">
        <x-page-title title="PnL Calendar" subtitle="Visualize your daily trading performance" />

        @if($hasTrades)
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1 block">Total PnL</span>
                    <span class="text-xl font-bold {{ $totalPnl >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $totalPnl >= 0 ? '+' : '-' }}${{ number_format(abs($totalPnl), 2) }}
                    </span>
                </div>
                <div class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1 block">Win Days</span>
                    <span class="text-xl font-bold text-green-500">{{ $winDays }}</span>
                </div>
                <div class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1 block">Loss Days</span>
                    <span class="text-xl font-bold text-red-500">{{ $lossDays }}</span>
                </div>
                <div class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-4">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1 block">Win Rate</span>
                    <span class="text-xl font-bold {{ $dayWinRate >= 50 ? 'text-blue-500' : 'text-red-500' }}">
                        {{ number_format($dayWinRate, 1) }}%
                    </span>
                </div>
            </div>

            <!-- Calendar -->
            <div class="bg-white dark:bg-[#1A1C23] border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                <!-- Month Navigation -->
                <div class="flex items-center justify-between mb-6">
                    <a href="?year={{ $prevYear }}&month={{ $prevMonth }}" 
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </a>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::create()->month($currentMonth)->year($currentYear)->format('F Y') }}
                    </h2>
                    <a href="?year={{ $nextYear }}&month={{ $nextMonth }}" 
                       class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-2">
                    <!-- Day Headers -->
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider py-2">
                            {{ $day }}
                        </div>
                    @endforeach

                    <!-- Calendar Days -->
                    @php
                        $firstDayOfMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
                        $startingDayOfWeek = $firstDayOfMonth->dayOfWeek;
                        $daysInMonth = $firstDayOfMonth->daysInMonth;
                    @endphp

                    {{-- Empty cells for days before the first of the month --}}
                    @for($i = 0; $i < $startingDayOfWeek; $i++)
                        <div class="aspect-square"></div>
                    @endfor

                    {{-- Actual days of the month --}}
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dateString = "{$currentYear}-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                            $dayData = $dailyPnl[$dateString] ?? null;
                            $pnl = $dayData?->pnl ?? 0;
                            $trades = $dayData?->trades_count ?? 0;
                            $isToday = $dateString === now()->format('Y-m-d');
                            
                            $bgClass = 'bg-gray-50 dark:bg-[#20222a]';
                            $textClass = 'text-gray-900 dark:text-white';
                            $borderClass = 'border-gray-200 dark:border-gray-800';
                            
                            if ($pnl > 0) {
                                $bgClass = 'bg-green-50 dark:bg-green-900/20';
                                $textClass = 'text-green-600 dark:text-green-400';
                                $borderClass = 'border-green-200 dark:border-green-800/30';
                            } elseif ($pnl < 0) {
                                $bgClass = 'bg-red-50 dark:bg-red-900/20';
                                $textClass = 'text-red-600 dark:text-red-400';
                                $borderClass = 'border-red-200 dark:border-red-800/30';
                            }
                        @endphp
                        <div class="aspect-square">
                            <div class="h-full w-full rounded-lg border {{ $borderClass }} {{ $bgClass }} p-2 flex flex-col justify-between hover:shadow-md transition-shadow cursor-pointer {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium {{ $trades > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $day }}
                                    </span>
                                    @if($trades > 0)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                            {{ $trades }}T
                                        </span>
                                    @endif
                                </div>
                                @if($trades > 0)
                                    <div class="text-sm font-bold {{ $pnl >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $pnl >= 0 ? '+' : '' }}${{ number_format(abs($pnl), 2) }}
                                    </div>
                                @else
                                    <div class="text-xs text-gray-400 dark:text-gray-500">No trades</div>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Legend -->
            <div class="flex items-center justify-center gap-6 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30"></div>
                    <span>Profitable Day</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30"></div>
                    <span>Loss Day</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-gray-50 dark:bg-[#20222a] border border-gray-200 dark:border-gray-800"></div>
                    <span>No Trades</span>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="mt-8">
                <div class="bg-gray-50 dark:bg-[#141414] border-2 border-dashed border-gray-300 dark:border-[#1F1F1E] rounded-xl p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-2">No trading data available</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Add some trades to see your PnL calendar.</p>
                    <a href="{{ route('trades.create') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 transition-colors">
                        Add Your First Trade
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
