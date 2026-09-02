<x-layouts.app title="Trade Logs - Tradexy">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-4 mb-8">
        <x-page-title title="Logs" subtitle="List of all your trades" />

        {{-- Active Filters Banner & Chips --}}
        @php
            $hasActiveFilters = !empty($startDate) || !empty($endDate) || !empty($dateFilter)
                || !empty($symbol) || !empty($outcome) || !empty($side)
                || !empty($strategyId) || !empty($timeframe) || !empty($hasChart) || !empty($hasAi);
            $activeStrategy = !empty($strategyId) ? $strategies->firstWhere('id', (int) $strategyId) : null;
        @endphp

        @if($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 p-3 bg-base-200/80 border border-base-300 rounded-xl text-xs">
                <span class="font-bold text-base-content/70 uppercase tracking-wider flex items-center gap-1.5 mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3.5 text-primary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                    Active Filters:
                </span>

                @if(!empty($symbol))
                    <span class="badge badge-primary badge-sm gap-1">
                        Symbol: <strong>{{ strtoupper($symbol) }}</strong>
                    </span>
                @endif

                @if(!empty($outcome))
                    <span class="badge badge-sm gap-1 {{ $outcome === 'win' ? 'badge-success text-success-content' : ($outcome === 'loss' ? 'badge-error text-error-content' : 'badge-neutral') }}">
                        Outcome: <strong>{{ ucfirst($outcome) }}</strong>
                    </span>
                @endif

                @if(!empty($side))
                    <span class="badge badge-sm gap-1 badge-info text-info-content">
                        Side: <strong>{{ strtoupper($side) }}</strong>
                    </span>
                @endif

                @if($activeStrategy)
                    <span class="badge badge-sm gap-1 badge-secondary text-secondary-content">
                        Strategy: <strong>{{ $activeStrategy->name }}</strong>
                    </span>
                @endif

                @if(!empty($timeframe))
                    <span class="badge badge-sm gap-1 badge-accent text-accent-content">
                        TF: <strong>{{ $timeframe }}</strong>
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

                @if(!empty($startDate) && !empty($endDate))
                    <span class="badge badge-sm gap-1 badge-neutral">
                        📅 {{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    </span>
                @elseif(!empty($startDate))
                    <span class="badge badge-sm gap-1 badge-neutral">
                        📅 From {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                    </span>
                @elseif(!empty($endDate))
                    <span class="badge badge-sm gap-1 badge-neutral">
                        📅 Up to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    </span>
                @elseif(!empty($dateFilter))
                    <span class="badge badge-sm gap-1 badge-neutral">
                        📅 {{ \Carbon\Carbon::parse($dateFilter)->format('M d, Y') }}
                    </span>
                @endif

                <a href="{{ route('trades.index') }}" class="ml-auto btn btn-ghost btn-xs text-error font-semibold hover:bg-error/10">
                    ✕ Clear all
                </a>
            </div>
        @endif

        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-4">
            <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                <a href="{{ route('trades.create') }}" class="btn btn-primary flex-1 sm:flex-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Trade
                </a>

                <button type="button" onclick="document.getElementById('trade_import_modal').showModal()" class="btn btn-outline flex-1 sm:flex-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    Import
                </button>

                <a href="{{ route('trades.export', request()->query()) }}" class="btn btn-outline flex-1 sm:flex-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export
                </a>
            </div>

            <!-- Comprehensive Filter Toolbar Form -->
            <form method="GET" action="{{ route('trades.index') }}" class="flex flex-wrap items-center gap-2 text-xs w-full xl:w-auto">
                {{-- Symbol Search --}}
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

                {{-- Outcome Selector --}}
                <select name="outcome" class="select select-xs select-bordered bg-base-100 flex-1 sm:flex-none" aria-label="Filter by Outcome">
                    <option value="">Outcome: All</option>
                    <option value="win" @selected($outcome === 'win')>🟢 Wins</option>
                    <option value="loss" @selected($outcome === 'loss')>🔴 Losses</option>
                    <option value="breakeven" @selected($outcome === 'breakeven')>⚪ Breakeven</option>
                </select>

                {{-- Side Selector --}}
                <select name="side" class="select select-xs select-bordered bg-base-100 flex-1 sm:flex-none" aria-label="Filter by Side">
                    <option value="">Side: All</option>
                    <option value="long" @selected($side === 'long')>Long (Buy)</option>
                    <option value="short" @selected($side === 'short')>Short (Sell)</option>
                </select>

                {{-- Date Range --}}
                <div class="flex items-center gap-1 bg-base-200/80 p-1 rounded-lg border border-base-300">
                    <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="input input-xs input-bordered w-28 bg-base-100" aria-label="Start Date" />
                    <span class="text-base-content/40 text-[10px]">to</span>
                    <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="input input-xs input-bordered w-28 bg-base-100" aria-label="End Date" />
                </div>

                {{-- More Filters Dropdown --}}
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-xs btn-outline gap-1 {{ (!empty($strategyId) || !empty($timeframe) || !empty($hasChart) || !empty($hasAi)) ? 'btn-primary' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        More
                    </div>
                    <div tabindex="0" class="dropdown-content z-30 menu p-4 shadow-2xl bg-base-100 border border-base-300 rounded-box w-72 space-y-3 mt-1">
                        <div class="font-bold text-xs uppercase tracking-wider text-base-content/70 pb-1 border-b border-base-200">Additional Filters</div>

                        <div class="space-y-1">
                            <label class="label-text text-[11px] font-semibold">Strategy</label>
                            <select name="strategy_id" class="select select-xs select-bordered w-full">
                                <option value="">All Strategies</option>
                                @foreach($strategies as $strategy)
                                    <option value="{{ $strategy->id }}" @selected((string)$strategyId === (string)$strategy->id)>{{ $strategy->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="label-text text-[11px] font-semibold">Timeframe</label>
                            <select name="timeframe" class="select select-xs select-bordered w-full">
                                <option value="">All Timeframes</option>
                                @foreach(['1m', '5m', '15m', '30m', '1hr', '4hr', '1d'] as $tf)
                                    <option value="{{ $tf }}" @selected($timeframe === $tf)>{{ $tf }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2 pt-1 border-t border-base-200">
                            <label class="label cursor-pointer py-0">
                                <span class="label-text text-xs">Chart Attached</span>
                                <input type="checkbox" name="has_chart" value="1" @checked(!empty($hasChart)) class="checkbox checkbox-xs checkbox-primary" />
                            </label>
                            <label class="label cursor-pointer py-0">
                                <span class="label-text text-xs">AI Analysis</span>
                                <input type="checkbox" name="has_ai" value="1" @checked(!empty($hasAi)) class="checkbox checkbox-xs checkbox-primary" />
                            </label>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-xs btn-primary w-full font-semibold">Apply All Filters</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-xs btn-primary font-semibold">Filter</button>
                @if($hasActiveFilters)
                    <a href="{{ route('trades.index') }}" class="btn btn-xs btn-ghost text-error" title="Clear All Filters">✕</a>
                @endif
            </form>
        </div>

        @if($ownedTrades->isNotEmpty())
            {{-- Dedicated Compact Bulk Action Banner (strictly 1 single line) --}}
            <div class="bulk-action-container hidden flex items-center justify-between gap-2 px-3 py-1.5 bg-base-200/90 border border-primary/25 shadow-sm rounded-xl mb-3 text-xs">
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="badge badge-primary font-bold text-xs" id="selected-count-badge">0 selected</span>
                    <span class="text-xs font-semibold text-base-content/70 hidden sm:inline">Bulk:</span>
                </div>
                <div class="flex items-center gap-1.5 flex-nowrap overflow-x-auto">
                    <select class="select select-xs select-bordered bg-base-100 w-28 text-xs shrink-0" name="timeframe" id="bulk-timeframe">
                        <option value="">Timeframe...</option>
                        <option>1m</option>
                        <option>5m</option>
                        <option>15m</option>
                        <option>30m</option>
                        <option>1hr</option>
                        <option>4hr</option>
                        <option>1d</option>
                    </select>
                    <select class="select select-xs select-bordered bg-base-100 w-32 text-xs shrink-0" name="strategy_id" id="bulk-strategy">
                        <option value="">Strategy...</option>
                        @foreach($strategies as $strategy)
                            <option value="{{ $strategy->id }}">{{ $strategy->name }}</option>
                        @endforeach
                    </select>

                    <button class="btn btn-xs btn-primary font-semibold shrink-0" id="apply-bulk">Update</button>
                    <div class="w-px h-4 bg-base-300 mx-0.5 shrink-0"></div>
                    <button class="btn btn-xs btn-error btn-outline font-semibold gap-1 shrink-0 px-2" id="bulk-delete" aria-label="Delete Selected">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Delete
                    </button>
                    <button type="button" class="btn btn-xs btn-ghost text-base-content/60 font-semibold shrink-0 px-1.5" id="deselect-all-btn">
                        Cancel
                    </button>
                </div>
            </div>

            <x-table>
                    <x-slot:header>
                        <th class="w-10">
                            <label>
                                <input type="checkbox" class="all-trade-checkbox size-4" />
                            </label>
                        </th>
                        <th>Date</th>
                        <th class="text-center">Symbol</th>
                        <th class="hidden sm:table-cell whitespace-nowrap">Duration</th>
                        <th class="hidden sm:table-cell whitespace-nowrap">Qty</th>
                        <th class="text-right pr-4 sm:pr-8 whitespace-nowrap">Net Pnl</th>
                        <th class="w-16 text-center">Chart</th>
                        <th class="w-16 text-center">AI</th>
                    </x-slot:header>
                    <tbody id="trades-table-body">
                        @foreach ($ownedTrades as $ownedTrade)
                            <x-table.row onclick="window.location='/trades/{{ $ownedTrade->id }}'">
                                <th onclick="event.stopPropagation()" class="w-10">
                                    <label>
                                        <input type="checkbox" class="trade-checkbox size-4" value="{{ $ownedTrade->id }}" />
                                    </label>
                                </th>
                                <td class="font-medium whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($ownedTrade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y') }}
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{ $ownedTrade->symbol }}
                                        <span
                                            class="badge badge-outline badge-xs uppercase">{{ $ownedTrade->market ?? 'crypto' }}</span>
                                        @if($ownedTrade->is_demo)
                                            <span class="badge badge-warning badge-xs uppercase">Demo</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell whitespace-nowrap">
                                    {{ $ownedTrade->duration }}
                                </td>
                                <td class="hidden sm:table-cell whitespace-nowrap">
                                    {{ strpos((string) $ownedTrade->quantity, '.') !== false ? rtrim(rtrim((string) $ownedTrade->quantity, '0'), '.') : $ownedTrade->quantity }}
                                </td>
                                <td class="text-right pr-4 sm:pr-8 whitespace-nowrap">
                                    <span @class([
                                        'font-bold',
                                        'text-green-400' => $ownedTrade->total_pnl > 0,
                                        'text-red-400' => $ownedTrade->total_pnl < 0
                                    ])>@currency($ownedTrade->total_pnl, $ownedTrade->market)</span>
                                </td>
                                <td class="w-16 text-center">
                                    @if($ownedTrade->chart_picture)
                                        <div class="tooltip tooltip-left" data-tip="Click trade to view chart">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor" class="size-5 text-gray-400 mx-auto">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="w-16 text-center">
                                    @if($ownedTrade->has_ai_analysis)
                                        <div class="tooltip tooltip-left" data-tip="AI Analysis Available (Click to view)">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor" class="size-5 text-indigo-400 mx-auto">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.456-2.454L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.454 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                            </x-table.row>
                        @endforeach
                    </tbody>
                </x-table>

                <div class="mt-4">
                    {{ $ownedTrades->links() }}
                </div>
            @else
                <div class="mt-8">
                    <div class="bg-base-200 border-2 border-dashed border-base-300 rounded-xl p-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-12 h-12 mx-auto text-base-content/40 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m-1.5 4.5h.008v.008H6.75V11.25Zm0 3h.008v.008H6.75V14.25Zm0 3h.008v.008H6.75V17.25ZM12 11.25h.008v.008H12V11.25Zm0 3h.008v.008H12V14.25Zm0 3h.008v.008H12V17.25ZM17.25 11.25h.008v.008h-.008V11.25Zm0 3h.008v.008h-.008V14.25Z" />
                        </svg>

                        <h3 class="font-bold text-lg text-base-content mb-2">No trades added yet</h3>
                        <p class="text-base-content/60 mb-6">Add your first trade manually or import from a CSV / Excel file to start analyzing your market edge.</p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('trades.create') }}" class="btn btn-primary">
                                + Add Trade
                            </a>
                            <button type="button" onclick="document.getElementById('trade_import_modal').showModal()" class="btn btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                Import CSV
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

<!-- Bulk Delete Confirmation Modal -->
<dialog id="bulk_delete_confirm_modal" class="modal">
    <div class="modal-box border-2 border-error/20 shadow-2xl">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-error/10 flex items-center justify-center text-error border border-error/20">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-black uppercase tracking-tight italic">Confirm <span class="text-error">Bulk Delete</span></h3>
                <p class="text-xs font-bold opacity-40 uppercase tracking-widest">Irreversible Security Action</p>
            </div>
        </div>
        
        <p class="text-sm font-medium opacity-70 mb-6 leading-relaxed">
            You are about to permanently delete <span id="delete-count-display" class="font-black text-error">0</span> selected trades. This action will remove all associated data including PnL, strategies, and AI analysis.
        </p>

        <div class="modal-action">
            <form method="dialog" class="flex gap-3 w-full">
                <button class="btn flex-1 font-black uppercase tracking-widest text-[10px]">Back</button>
                <button type="button" id="bulk-delete-confirm-btn" onclick="executeBulkDelete()" class="btn btn-error flex-2 font-black uppercase tracking-widest text-[10px] px-8">Confirm Delete</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Trade Import Modal -->
<dialog id="trade_import_modal" class="modal">
    <div class="modal-box max-w-3xl border border-base-300 shadow-2xl p-6">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
        </form>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">Import Trades</h3>
                <p class="text-xs text-base-content/60">Upload trades via CSV or Excel-compatible spreadsheet</p>
            </div>
        </div>

        <!-- Instructions & Table Guide -->
        <div class="bg-base-200/60 rounded-xl p-4 border border-base-300 mb-5 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <span class="font-bold text-xs uppercase tracking-wider text-base-content/80">CSV / Excel Column Reference</span>
                    <p class="text-[11px] text-base-content/60">Include these column headers in the first row of your CSV file.</p>
                </div>
                <a href="{{ route('trades.template') }}" class="btn btn-xs btn-primary btn-outline gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download Sample Template
                </a>
            </div>

            <!-- Tabular Column Guide -->
            <div class="overflow-x-auto max-h-56 rounded-lg border border-base-300 bg-base-100">
                <table class="table table-xs table-pin-rows w-full text-left">
                    <thead>
                        <tr class="bg-base-200/90 text-base-content/70">
                            <th class="font-semibold">Column Name</th>
                            <th class="font-semibold">Status</th>
                            <th class="font-semibold">Example Value</th>
                            <th class="font-semibold">Description / Format</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200 text-[11px]">
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono font-bold text-primary">symbol</td>
                            <td><span class="badge badge-primary badge-xs">Required</span></td>
                            <td class="font-mono text-base-content/80">BTCUSDT / ALI</td>
                            <td class="text-base-content/70">Trading pair or stock ticker</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono font-bold text-primary">avg_entry_price</td>
                            <td><span class="badge badge-primary badge-xs">Required</span></td>
                            <td class="font-mono text-base-content/80">64500.50</td>
                            <td class="text-base-content/70">Entry execution price (> 0)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono font-bold text-primary">quantity</td>
                            <td><span class="badge badge-primary badge-xs">Required</span></td>
                            <td class="font-mono text-base-content/80">0.25 / 1000</td>
                            <td class="text-base-content/70">Trade volume / quantity (> 0)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono font-bold text-primary">open_datetime</td>
                            <td><span class="badge badge-primary badge-xs">Required</span></td>
                            <td class="font-mono text-base-content/80">2026-03-01 10:00:00</td>
                            <td class="text-base-content/70">Date & time trade opened (YYYY-MM-DD HH:MM:SS)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">market</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">crypto / pse</td>
                            <td class="text-base-content/70">Market type (default: <code>crypto</code>)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">entry_side</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">long / short</td>
                            <td class="text-base-content/70">Entry direction (default: <code>long</code>)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">avg_exit_price</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">66200.00</td>
                            <td class="text-base-content/70">Exit execution price (leave blank if trade is open)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">close_datetime</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">2026-03-01 16:30:00</td>
                            <td class="text-base-content/70">Date & time trade closed</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">strategy</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">Breakout Master</td>
                            <td class="text-base-content/70">Matches existing strategy name in your journal</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">leverage</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">5</td>
                            <td class="text-base-content/70">Leverage multiplier (1-500, default: <code>1</code>)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">timeframe</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">1hr / 4hr / 1d</td>
                            <td class="text-base-content/70">Chart timeframe (1m, 5m, 15m, 30m, 1hr, 4hr, 1d, 1w)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">entry_reasons</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">Support bounce; 4H breakout</td>
                            <td class="text-base-content/70">Multiple reasons separated with semicolons (<code>;</code>)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">exit_reasons</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">Target reached; Bearish wick</td>
                            <td class="text-base-content/70">Multiple reasons separated with semicolons (<code>;</code>)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">lessons</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">Followed plan; Good patience</td>
                            <td class="text-base-content/70">Lessons learned separated with semicolons (<code>;</code>)</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">chart_picture</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">https://.../chart.png</td>
                            <td class="text-base-content/70">URL link to trade setup chart image</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">ai_analysis</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">Bullish market bias...</td>
                            <td class="text-base-content/70">AI analysis notes or summary</td>
                        </tr>
                        <tr class="hover:bg-base-200/50">
                            <td class="font-mono text-base-content">is_demo</td>
                            <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                            <td class="font-mono text-base-content/80">0 / 1</td>
                            <td class="text-base-content/70"><code>1</code> for demo trade, <code>0</code> for real (default: <code>0</code>)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Sample Spreadsheet Mockup -->
            <div class="border border-base-300 rounded-lg overflow-hidden bg-base-100 text-[10px]">
                <div class="bg-base-200 px-3 py-1.5 font-bold uppercase tracking-wider text-base-content/60 flex items-center justify-between">
                    <span>Spreadsheet Preview (Example Rows)</span>
                    <span class="badge badge-xs badge-neutral">Row 1 = Headers</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-xs whitespace-nowrap font-mono">
                        <thead class="bg-base-300/60 text-base-content font-bold">
                            <tr>
                                <th class="bg-base-300/90 text-center w-8">#</th>
                                <th>symbol</th>
                                <th>market</th>
                                <th>avg_entry_price</th>
                                <th>quantity</th>
                                <th>avg_exit_price</th>
                                <th>open_datetime</th>
                                <th>close_datetime</th>
                                <th>strategy</th>
                                <th>chart_picture</th>
                                <th>ai_analysis</th>
                                <th>entry_reasons</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200">
                            <tr class="hover:bg-base-200/40">
                                <td class="text-center font-bold bg-base-200/60 text-base-content/50">1</td>
                                <td class="text-primary font-bold">BTCUSDT</td>
                                <td>crypto</td>
                                <td>64500.00</td>
                                <td>0.25</td>
                                <td>66200.00</td>
                                <td>2026-03-01 10:00:00</td>
                                <td>2026-03-01 16:30:00</td>
                                <td>Trend Following</td>
                                <td class="truncate max-w-xs">https://storage.../chart1.png</td>
                                <td class="truncate max-w-xs">Bullish breakout with 4H support...</td>
                                <td>Support retest; Volume breakout</td>
                            </tr>
                            <tr class="hover:bg-base-200/40">
                                <td class="text-center font-bold bg-base-200/60 text-base-content/50">2</td>
                                <td class="text-primary font-bold">ALI</td>
                                <td>pse</td>
                                <td>29.50</td>
                                <td>1000</td>
                                <td>32.00</td>
                                <td>2026-02-28 09:35:00</td>
                                <td>2026-03-01 14:45:00</td>
                                <td>Breakout Strategy</td>
                                <td class="truncate max-w-xs">https://storage.../chart2.png</td>
                                <td class="truncate max-w-xs">Cup and handle pattern breakout...</td>
                                <td>Cup and handle; High volume</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <form id="trade-import-form" action="{{ route('trades.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Drag and Drop Dropzone -->
            <div id="dropzone-area" class="border-2 border-dashed border-base-300 hover:border-primary rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 bg-base-100 flex flex-col items-center justify-center gap-2 group">
                <input type="file" name="file" id="trade-file-input" accept=".csv,.txt" class="hidden" required />

                <div id="dropzone-idle" class="flex flex-col items-center gap-2 py-2">
                    <div class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center text-base-content/60 group-hover:text-primary group-hover:scale-110 group-hover:bg-primary/10 transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-base-content">
                        <span class="text-primary hover:underline">Click to browse</span> or drag & drop CSV file
                    </p>
                    <p class="text-xs text-base-content/50">Supports standard CSV (.csv, .txt) up to 10MB</p>
                </div>

                <!-- Selected File Display -->
                <div id="dropzone-selected" class="hidden flex items-center justify-between w-full p-3 bg-base-200 rounded-xl border border-base-300">
                    <div class="flex items-center gap-3 overflow-hidden text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-primary shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <div class="truncate">
                            <p id="selected-file-name" class="text-xs font-bold text-base-content truncate">filename.csv</p>
                            <p id="selected-file-size" class="text-[10px] text-base-content/50">0 KB</p>
                        </div>
                    </div>
                    <button type="button" id="remove-file-btn" class="btn btn-ghost btn-xs btn-circle text-error" title="Remove file">✕</button>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('trade_import_modal').close()" class="btn btn-ghost">Cancel</button>
                <button type="submit" id="submit-import-btn" class="btn btn-primary" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    <span id="import-btn-text">Import Trades</span>
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    (function () {
    let bulkBtn = document.getElementById('apply-bulk')
    let allTradesBtn = document.querySelector('.all-trade-checkbox')
    let singleTradeCheckbox = document.querySelectorAll('.trade-checkbox')
    let bulkContainer = document.querySelector('.bulk-action-container')
    let bulkDeleteBtn = document.getElementById('bulk-delete')

    if (!bulkBtn || !allTradesBtn || !bulkContainer || !bulkDeleteBtn) {
        return;
    }

    const deselectBtn = document.getElementById('deselect-all-btn');

    function updateBulkDisplay() {
        const checkedCount = document.querySelectorAll('.trade-checkbox:checked').length;
        const countBadge = document.getElementById('selected-count-badge');

        if (countBadge) {
            countBadge.textContent = checkedCount + (checkedCount === 1 ? ' selected' : ' selected');
        }

        if (checkedCount === 0) {
            bulkContainer.classList.add('hidden');
            allTradesBtn.checked = false;
        } else {
            bulkContainer.classList.remove('hidden');
            allTradesBtn.checked = (checkedCount === singleTradeCheckbox.length && singleTradeCheckbox.length > 0);
        }
    }

    // Select a checkbox then update display
    for (let i = 0; i < singleTradeCheckbox.length; i++) {
        singleTradeCheckbox[i].addEventListener('change', updateBulkDisplay);
    }

    function selectAllTrades() {
        let isChecked = allTradesBtn.checked;
        let allTradesCheckbox = document.querySelectorAll('.trade-checkbox');

        for (let i = 0; i < allTradesCheckbox.length; i++) {
            allTradesCheckbox[i].checked = isChecked;
        }

        updateBulkDisplay();
    }

    deselectBtn?.addEventListener('click', () => {
        allTradesBtn.checked = false;
        selectAllTrades();
    });

    function executeBulkDelete() {
        submitBulkAction('delete');
    }
    window.executeBulkDelete = executeBulkDelete;

    function performBulkAction(action) {
        let checkedTrades = document.querySelectorAll('.trade-checkbox:checked');
        let tradeIds = Array.from(checkedTrades).map(cb => cb.value);

        if (tradeIds.length === 0) {
            if (window.showToast) {
                window.showToast('Please select at least one trade first.', 'error');
            } else {
                alert('Please select at least one trade first.');
            }
            return;
        }

        if (action === 'delete') {
            document.getElementById('delete-count-display').textContent = tradeIds.length;
            document.getElementById('bulk_delete_confirm_modal').showModal();
            return;
        }

        submitBulkAction('update');
    }

    function submitBulkAction(action) {
        let checkedTrades = document.querySelectorAll('.trade-checkbox:checked');
        let tradeIds = Array.from(checkedTrades).map(cb => cb.value);

        let payload = {
            trade_ids: tradeIds,
            action: action,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        if (action === 'update') {
            payload.timeframe = document.getElementById('bulk-timeframe').value;
            payload.strategy_id = document.getElementById('bulk-strategy').value;

            if (!payload.timeframe && !payload.strategy_id) {
                if (window.showToast) {
                    window.showToast('Please select a timeframe or strategy to update.', 'error');
                } else {
                    alert('Please select a timeframe or strategy to update.');
                }
                return;
            }
        }

        // We use a hidden form to submit so we can handle redirection and session messages easily
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('trades.bulk') }}";

        for (let key in payload) {
            if (Array.isArray(payload[key])) {
                payload[key].forEach(val => {
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `${key}[]`;
                    input.value = val;
                    form.appendChild(input);
                });
            } else {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = payload[key];
                form.appendChild(input);
            }
        }

        document.body.appendChild(form);
        form.submit();
    }

    bulkBtn.addEventListener('click', () => performBulkAction('update'));
    bulkDeleteBtn.addEventListener('click', () => performBulkAction('delete'));
    const confirmDeleteBtn = document.getElementById('bulk-delete-confirm-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', executeBulkDelete);
    }
    allTradesBtn.addEventListener('click', selectAllTrades);

    // Real-time Event Listener
    if (window.Echo && !window.__tradeIndexEchoInit) {
        window.__tradeIndexEchoInit = true;
        window.Echo.private("App.Models.User." + @js(auth()->id()))
            .listen('.NewTradesFetched', (e) => {
                console.log('Real-time event received:', e);
                if (window.showToast) {
                    window.showToast(e.message || 'New trades fetched!', 'success');
                }
                refreshTradeLog();
            });
    }

    async function refreshTradeLog() {
        try {
            const response = await fetch("{{ route('trades.index') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            // Re-render table rows
            const tbody = document.getElementById('trades-table-body');
            if (tbody && data.ownedTrades && data.ownedTrades.data) {
                tbody.innerHTML = data.ownedTrades.data.map(trade => {
                    const closeDate = new Date(trade.close_datetime);
                    const formattedDate = closeDate.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    const pnlClass = trade.total_pnl > 0 ? 'text-green-400' : (trade.total_pnl < 0 ? 'text-red-400' : '');
                    const formattedPnl = trade.formatted_pnl;
                    
                    return `
                        <tr class="border-b border-base-300 odd:bg-base-200 even:bg-base-100 hover:bg-base-300 transition-colors text-center h-12 [&>th]:px-2 [&>th]:sm:px-4 [&>td]:px-2 [&>td]:sm:px-4 [&>th:first-child]:pl-4 [&>td:last-child]:pr-4 cursor-pointer" onclick="window.location='/trades/${trade.id}'">
                            <th onclick="event.stopPropagation()">
                                <label>
                                    <input type="checkbox" class="trade-checkbox size-4" value="${trade.id}" />
                                </label>
                            </th>
                            <td class="font-medium whitespace-nowrap">${formattedDate}</td>
                            <td>
                                <div class="flex items-center justify-center gap-2">
                                    ${trade.symbol}
                                    <span class="badge badge-outline badge-xs uppercase">${trade.market || 'crypto'}</span>
                                    ${trade.is_demo ? '<span class="badge badge-warning badge-xs uppercase">Demo</span>' : ''}
                                </div>
                            </td>
                            <td class="hidden sm:table-cell">${trade.duration || 'N/A'}</td>
                            <td class="hidden sm:table-cell">${parseFloat(trade.quantity)}</td>
                            <td class="text-right pr-4 sm:pr-8 whitespace-nowrap"><span class="font-bold ${pnlClass}">${formattedPnl}</span></td>
                            <td class="w-16 text-center">
                                ${trade.chart_picture ? `
                                    <div class="tooltip tooltip-left" data-tip="Click trade to view chart">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-400 mx-auto">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    </div>
                                ` : '-'}
                            </td>
                            <td class="w-16 text-center">
                                ${trade.has_ai_analysis ? `
                                    <div class="tooltip tooltip-left" data-tip="AI Analysis Available (Click trade to view)">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-400 mx-auto">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.456-2.454L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.454 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                        </svg>
                                    </div>
                                ` : '-'}
                            </td>
                        </tr>
                    `;
                }).join('');

                // Re-bind checkbox listeners since we replaced the HTML
                bindCheckboxListeners();
            }
        } catch (error) {
            console.error('Error refreshing trade log:', error);
        }
    }

    function bindCheckboxListeners() {
        singleTradeCheckbox = document.querySelectorAll('.trade-checkbox');
        for (let i = 0; i < singleTradeCheckbox.length; i++) {
            singleTradeCheckbox[i].addEventListener('click', function () {
                let checkedCount = document.querySelectorAll('.trade-checkbox:checked').length
                if (checkedCount == 0) {
                    bulkContainer.classList.add('hidden')
                    allTradesBtn.checked = false
                } else {
                    bulkContainer.classList.remove('hidden')
                }
            });
        }
    }

    // Trade Import Modal Drag & Drop Logic
    const dropzone = document.getElementById('dropzone-area');
    const fileInput = document.getElementById('trade-file-input');
    const dropzoneIdle = document.getElementById('dropzone-idle');
    const dropzoneSelected = document.getElementById('dropzone-selected');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const removeFileBtn = document.getElementById('remove-file-btn');
    const submitImportBtn = document.getElementById('submit-import-btn');
    const importForm = document.getElementById('trade-import-form');

    if (dropzone && fileInput) {
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function handleFile(file) {
            if (!file) return;
            const validExtensions = ['.csv', '.txt'];
            const fileNameLower = file.name.toLowerCase();
            const isValid = validExtensions.some(ext => fileNameLower.endsWith(ext));

            if (!isValid) {
                if (window.showToast) {
                    window.showToast('Please select a valid CSV file (.csv or .txt)', 'error');
                } else {
                    alert('Please select a valid CSV file (.csv or .txt)');
                }
                return;
            }

            selectedFileName.textContent = file.name;
            selectedFileSize.textContent = formatBytes(file.size);
            dropzoneIdle.classList.add('hidden');
            dropzoneSelected.classList.remove('hidden');
            dropzone.classList.add('border-primary', 'bg-primary/5');
            if (submitImportBtn) submitImportBtn.disabled = false;
        }

        function resetDropzone() {
            fileInput.value = '';
            dropzoneIdle.classList.remove('hidden');
            dropzoneSelected.classList.add('hidden');
            dropzone.classList.remove('border-primary', 'bg-primary/5');
            if (submitImportBtn) submitImportBtn.disabled = true;
        }

        dropzone.addEventListener('click', (e) => {
            if (e.target.closest('#remove-file-btn')) return;
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                handleFile(e.target.files[0]);
            }
        });

        removeFileBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            resetDropzone();
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('border-primary', 'bg-primary/10', 'scale-[1.01]');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('border-primary', 'bg-primary/10', 'scale-[1.01]');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                handleFile(files[0]);
            }
        });

        importForm?.addEventListener('submit', () => {
            if (submitImportBtn) {
                submitImportBtn.disabled = true;
                submitImportBtn.innerHTML = `
                    <span class="loading loading-spinner loading-xs"></span>
                    <span>Importing...</span>
                `;
            }
        });
    }
    })();
</script>
</x-layouts.app>