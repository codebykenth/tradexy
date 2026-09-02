<x-layouts.app title="PnL Calendar - Tradexy">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-6 mb-8 mt-6">
        <x-page-title title="PnL Calendar" subtitle="Visualize your daily trading performance" />

        @php
            $hasActiveFilters = !empty($symbol) || !empty($side) || !empty($strategyId) || !empty($timeframe) || !empty($hasChart) || !empty($hasAi);
            $selectedStrategyName = !empty($strategyId) ? ($strategies->firstWhere('id', (int) $strategyId)?->name ?? 'Strategy #'.$strategyId) : null;
        @endphp

        {{-- Active Filters Badges --}}
        @if($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 bg-base-200/50 p-2.5 rounded-lg border border-base-300 text-xs">
                <span class="font-semibold text-base-content/70">Active filters:</span>

                @if(!empty($symbol))
                    <span class="badge badge-sm gap-1 badge-primary">
                        Ticker: {{ strtoupper($symbol) }}
                    </span>
                @endif

                @if(!empty($side))
                    <span class="badge badge-sm gap-1 {{ $side === 'long' ? 'badge-success' : 'badge-error' }} text-white">
                        {{ ucfirst($side) }}
                    </span>
                @endif

                @if(!empty($selectedStrategyName))
                    <span class="badge badge-sm gap-1 badge-neutral">
                        Strategy: {{ $selectedStrategyName }}
                    </span>
                @endif

                @if(!empty($timeframe))
                    <span class="badge badge-sm gap-1 badge-outline">
                        TF: {{ $timeframe }}
                    </span>
                @endif

                @if(!empty($hasChart))
                    <span class="badge badge-sm gap-1 badge-outline">
                        📷 With Chart
                    </span>
                @endif

                @if(!empty($hasAi))
                    <span class="badge badge-sm gap-1 badge-outline">
                        ✨ AI Analyzed
                    </span>
                @endif

                <a href="{{ route('pnl-calendar.index', ['year' => $currentYear, 'month' => $currentMonth]) }}" class="ml-auto btn btn-ghost btn-xs text-error font-semibold hover:bg-error/10">
                    ✕ Clear filters
                </a>
            </div>
        @endif

        {{-- Filter & Navigation Toolbar --}}
        <div class="bg-base-100 border border-base-300 rounded-xl p-3 sm:p-4 shadow-sm">
            <form method="GET" action="{{ route('pnl-calendar.index') }}" class="flex flex-wrap items-center justify-between gap-3">
                <input type="hidden" name="year" value="{{ $currentYear }}" />
                <input type="hidden" name="month" value="{{ $currentMonth }}" />

                <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                    {{-- Symbol Filter --}}
                    <div class="relative w-full sm:w-36">
                        <input
                            type="text"
                            name="symbol"
                            value="{{ $symbol ?? '' }}"
                            placeholder="Symbol (e.g. BTC)"
                            class="input input-xs input-bordered w-full pr-6 bg-base-100 uppercase"
                            aria-label="Filter by Symbol"
                        />
                        @if(!empty($symbol))
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-primary font-bold text-[10px]">●</span>
                        @endif
                    </div>

                    {{-- Strategy Filter --}}
                    <select name="strategy_id" class="select select-xs select-bordered bg-base-100 flex-1 sm:flex-none" aria-label="Filter by Strategy">
                        <option value="">Strategy: All</option>
                        @foreach($strategies as $strat)
                            <option value="{{ $strat->id }}" @selected((string) $strategyId === (string) $strat->id)>{{ $strat->name }}</option>
                        @endforeach
                    </select>

                    {{-- Side Filter --}}
                    <select name="side" class="select select-xs select-bordered bg-base-100 flex-1 sm:flex-none" aria-label="Filter by Side">
                        <option value="">Side: All</option>
                        <option value="long" @selected($side === 'long')>📈 Long</option>
                        <option value="short" @selected($side === 'short')>📉 Short</option>
                    </select>

                    {{-- Timeframe Filter --}}
                    @php
                        $standardTfs = ['1s', '1m', '2m', '3m', '5m', '10m', '15m', '30m', '45m', '1h', '2h', '3h', '4h', '6h', '8h', '12h', '1d', '2d', '3d', '1w', '1M'];
                    @endphp
                    <select name="timeframe" class="select select-xs select-bordered bg-base-100 flex-1 sm:flex-none" aria-label="Filter by Timeframe">
                        <option value="">Timeframe: All</option>
                        @if(!empty($timeframe) && !in_array($timeframe, $standardTfs))
                            <option value="{{ $timeframe }}" selected>{{ $timeframe }} (Custom)</option>
                        @endif
                        <optgroup label="Minutes">
                            @foreach(['1s', '1m', '2m', '3m', '5m', '10m', '15m', '30m', '45m'] as $tf)
                                <option value="{{ $tf }}" @selected($timeframe === $tf)>{{ $tf }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Hours">
                            @foreach(['1h', '2h', '3h', '4h', '6h', '8h', '12h'] as $tf)
                                <option value="{{ $tf }}" @selected($timeframe === $tf)>{{ $tf }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Days & Higher">
                            @foreach(['1d', '2d', '3d', '1w', '1M'] as $tf)
                                <option value="{{ $tf }}" @selected($timeframe === $tf)>{{ $tf }}</option>
                            @endforeach
                        </optgroup>
                    </select>

                    {{-- Quick Checkbox Toggles --}}
                    <label class="cursor-pointer label gap-1 py-0 px-1 border border-base-300 rounded bg-base-100 h-6">
                        <input type="checkbox" name="has_chart" value="1" @checked(!empty($hasChart)) class="checkbox checkbox-xs checkbox-primary" />
                        <span class="label-text text-[11px] font-medium text-base-content/80">📷 Chart</span>
                    </label>

                    <label class="cursor-pointer label gap-1 py-0 px-1 border border-base-300 rounded bg-base-100 h-6">
                        <input type="checkbox" name="has_ai" value="1" @checked(!empty($hasAi)) class="checkbox checkbox-xs checkbox-primary" />
                        <span class="label-text text-[11px] font-medium text-base-content/80">✨ AI</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    <button type="submit" class="btn btn-primary btn-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                        Apply Filter
                    </button>

                    @if($hasActiveFilters)
                        <a href="{{ route('pnl-calendar.index', ['year' => $currentYear, 'month' => $currentMonth]) }}" class="btn btn-ghost btn-xs text-base-content/70">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($hasTrades)
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-base-100 border border-base-300 rounded-xl p-4 shadow-sm">
                    <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1 block">Total PnL</span>
                    <span class="text-xl font-bold {{ $totalPnl >= 0 ? 'text-success' : 'text-error' }}">
                        {{ $totalPnl >= 0 ? '+' : '-' }}${{ number_format(abs($totalPnl), 2) }}
                    </span>
                </div>
                <div class="bg-base-100 border border-base-300 rounded-xl p-4 shadow-sm">
                    <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1 block">Win Days</span>
                    <span class="text-xl font-bold text-success">{{ $winDays }}</span>
                </div>
                <div class="bg-base-100 border border-base-300 rounded-xl p-4 shadow-sm">
                    <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1 block">Loss Days</span>
                    <span class="text-xl font-bold text-error">{{ $lossDays }}</span>
                </div>
                <div class="bg-base-100 border border-base-300 rounded-xl p-4 shadow-sm">
                    <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-1 block">Win Rate</span>
                    <span class="text-xl font-bold {{ $dayWinRate >= 50 ? 'text-primary' : 'text-error' }}">
                        {{ number_format($dayWinRate, 1) }}%
                    </span>
                </div>
            </div>

            <!-- Calendar Container -->
            <div class="bg-base-100 border border-base-300 rounded-xl p-4 sm:p-6 shadow-sm">
                <!-- Month Navigation Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-base-200">
                    <div class="flex items-center gap-2">
                        {{-- Previous Month Button --}}
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['year' => $prevYear, 'month' => $prevMonth])) }}" 
                           class="btn btn-ghost btn-sm btn-square hover:bg-base-200 transition-colors"
                           title="Previous Month"
                           aria-label="Previous Month">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </a>

                        {{-- Direct Month & Year Dropdown Selectors --}}
                        <div class="flex items-center gap-1.5">
                            <select 
                                id="calendar-month-select" 
                                class="select select-sm select-bordered bg-base-100 font-bold text-sm min-w-[9.5rem] w-38"
                                onchange="jumpToCalendarDate()"
                                aria-label="Select Month"
                            >
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected($currentMonth === $m)>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>

                            <select 
                                id="calendar-year-select" 
                                class="select select-sm select-bordered bg-base-100 font-bold text-sm min-w-[6rem] w-24"
                                onchange="jumpToCalendarDate()"
                                aria-label="Select Year"
                            >
                                @for($y = max(2020, now()->year - 5); $y <= max(now()->year + 2, $currentYear); $y++)
                                    <option value="{{ $y }}" @selected($currentYear === $y)>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Next Month Button --}}
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['year' => $nextYear, 'month' => $nextMonth])) }}" 
                           class="btn btn-ghost btn-sm btn-square hover:bg-base-200 transition-colors"
                           title="Next Month"
                           aria-label="Next Month">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </div>

                    {{-- Quick "This Month" Jump Shortcut --}}
                    @if($currentMonth !== (int) now()->month || $currentYear !== (int) now()->year)
                        <a href="?{{ http_build_query(array_merge(request()->query(), ['year' => now()->year, 'month' => now()->month])) }}" 
                           class="btn btn-outline btn-xs gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            This Month
                        </a>
                    @endif
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                    <!-- Day Headers -->
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="text-center text-xs font-semibold text-base-content/60 uppercase tracking-wider py-2">
                            <span class="sm:hidden">{{ substr($day, 0, 1) }}</span>
                            <span class="hidden sm:inline">{{ $day }}</span>
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
                            $dateString = "{$currentYear}-" . str_pad((string) $currentMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
                            $dayData = $dailyPnl[$dateString] ?? null;
                            $pnl = (float) ($dayData?->pnl ?? 0);
                            $trades = (int) ($dayData?->trades_count ?? 0);
                            $isToday = $dateString === now()->format('Y-m-d');
                            
                            $bgClass = 'bg-base-200/40 hover:bg-base-200/70';
                            $textClass = 'text-base-content';
                            $borderClass = 'border-base-300';
                            
                            if ($pnl > 0) {
                                $bgClass = 'bg-success/15 hover:bg-success/25 border-success/30';
                                $textClass = 'text-success font-bold';
                            } elseif ($pnl < 0) {
                                $bgClass = 'bg-error/15 hover:bg-error/25 border-error/30';
                                $textClass = 'text-error font-bold';
                            }
                        @endphp
                        <div class="aspect-square">
                            {{-- Link to trades.index filtered by this date and preserving active filters --}}
                            <a href="{{ $trades > 0 ? route('trades.index', array_filter(array_merge(request()->except(['year', 'month']), ['date' => $dateString]))) : '#' }}"
                               class="h-full w-full rounded-lg border {{ $borderClass }} {{ $bgClass }} p-1 sm:p-2 flex flex-col justify-between hover:shadow-md transition-all {{ $trades > 0 ? 'cursor-pointer' : 'cursor-default' }} {{ $isToday ? 'ring-2 ring-primary' : '' }} block">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold {{ $trades > 0 ? 'text-base-content' : 'text-base-content/40' }}">
                                        {{ $day }}
                                    </span>
                                    @if($trades > 0)
                                        <span class="text-[9px] sm:text-[10px] px-1 sm:px-1.5 py-0.5 rounded-full bg-base-300/80 text-base-content font-medium">
                                            {{ $trades }}<span class="hidden sm:inline">T</span>
                                        </span>
                                    @endif
                                </div>
                                @if($trades > 0)
                                    <div class="hidden sm:block text-xs sm:text-sm {{ $textClass }} truncate">
                                        {{ $pnl >= 0 ? '+' : '-' }}${{ number_format(abs($pnl), 2) }}
                                    </div>
                                @else
                                    <div class="hidden sm:block text-[10px] sm:text-xs text-base-content/30 truncate">No trades</div>
                                @endif
                            </a>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Legend -->
            <div class="flex items-center justify-center gap-6 text-xs text-base-content/70">
                <div class="flex items-center gap-2">
                    <div class="w-3.5 h-3.5 rounded bg-success/20 border border-success/40"></div>
                    <span>Profitable Day</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3.5 h-3.5 rounded bg-error/20 border border-error/40"></div>
                    <span>Loss Day</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3.5 h-3.5 rounded bg-base-200 border border-base-300"></div>
                    <span>No Trades</span>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="mt-8">
                <div class="bg-base-200/50 border-2 border-dashed border-base-300 rounded-xl p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-base-content/40 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <h3 class="font-bold text-lg text-base-content mb-2">No trading data available</h3>
                    <p class="text-base-content/60 text-sm mb-4">
                        @if($hasActiveFilters)
                            No trades match the current filter criteria for this month.
                        @else
                            Add some trades to see your PnL calendar.
                        @endif
                    </p>
                    @if($hasActiveFilters)
                        <a href="{{ route('pnl-calendar.index', ['year' => $currentYear, 'month' => $currentMonth]) }}" class="btn btn-outline btn-sm">
                            Clear Filters
                        </a>
                    @else
                        <a href="{{ route('trades.create') }}" class="btn btn-primary btn-sm">
                            Add Your First Trade
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        function jumpToCalendarDate() {
            const monthSelect = document.getElementById('calendar-month-select');
            const yearSelect = document.getElementById('calendar-year-select');
            if (!monthSelect || !yearSelect) return;

            const url = new URL(window.location.href);
            url.searchParams.set('month', monthSelect.value);
            url.searchParams.set('year', yearSelect.value);
            window.location.href = url.toString();
        }
    </script>
</x-layouts.app>
