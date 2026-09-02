<x-layouts.app title="Balances - Tradexy">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-4 mb-8">
        <x-page-title title="Balance & Equity History" />

        {{-- Active Balance Filters Banner & Chips --}}
        @php
            $hasActiveBalanceFilters = !empty($startDate) || !empty($endDate) || !empty($dateFilter)
                || !empty($pnlTrend) || $minEquity !== null || $maxEquity !== null;
        @endphp

        @if($hasActiveBalanceFilters)
            <div class="flex flex-wrap items-center gap-2 p-3 bg-base-200/80 border border-base-300 rounded-xl text-xs">
                <span class="font-bold text-base-content/70 uppercase tracking-wider flex items-center gap-1.5 mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3.5 text-primary">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                    Active Filters:
                </span>

                @if(!empty($pnlTrend))
                    <span class="badge badge-sm gap-1 {{ $pnlTrend === 'profit' ? 'badge-success text-success-content' : ($pnlTrend === 'loss' ? 'badge-error text-error-content' : 'badge-neutral') }}">
                        PnL: <strong>{{ $pnlTrend === 'profit' ? 'Profitable Days' : ($pnlTrend === 'loss' ? 'Loss Days' : 'Breakeven') }}</strong>
                    </span>
                @endif

                @if($minEquity !== null && $maxEquity !== null)
                    <span class="badge badge-sm gap-1 badge-primary">
                        Equity: <strong>{{ number_format($minEquity) }} - {{ number_format($maxEquity) }}</strong>
                    </span>
                @elseif($minEquity !== null)
                    <span class="badge badge-sm gap-1 badge-primary">
                        Min Equity: <strong>{{ number_format($minEquity) }}</strong>
                    </span>
                @elseif($maxEquity !== null)
                    <span class="badge badge-sm gap-1 badge-primary">
                        Max Equity: <strong>{{ number_format($maxEquity) }}</strong>
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

                <a href="{{ route('balances.index') }}" class="ml-auto btn btn-ghost btn-xs text-error font-semibold hover:bg-error/10">
                    ✕ Clear all
                </a>
            </div>
        @endif

        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-4">
            <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto">
                <a href="{{ route('balances.create') }}" class="btn btn-primary flex-1 sm:flex-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Entry
                </a>

                <button type="button" onclick="document.getElementById('balance_import_modal').showModal()" class="btn btn-outline flex-1 sm:flex-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    Import
                </button>

                <a href="{{ route('balances.export', request()->query()) }}" class="btn btn-outline flex-1 sm:flex-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export
                </a>
            </div>

            <!-- Comprehensive Balance Filter Toolbar -->
            <form method="GET" action="{{ route('balances.index') }}" class="flex flex-wrap items-center gap-2 text-xs w-full xl:w-auto">
                {{-- PnL Trend Selector --}}
                <select name="pnl_trend" class="select select-xs select-bordered bg-base-100" aria-label="Filter by PnL Trend">
                    <option value="">PnL: All</option>
                    <option value="profit" @selected($pnlTrend === 'profit')>🟢 Profit Only</option>
                    <option value="loss" @selected($pnlTrend === 'loss')>🔴 Loss Only</option>
                    <option value="breakeven" @selected($pnlTrend === 'breakeven')>⚪ Breakeven</option>
                </select>

                {{-- Date Range --}}
                <div class="flex items-center gap-1 bg-base-200/80 p-1 rounded-lg border border-base-300">
                    <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="input input-xs input-bordered w-28 bg-base-100" aria-label="Start Date" />
                    <span class="text-base-content/40 text-[10px]">to</span>
                    <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="input input-xs input-bordered w-28 bg-base-100" aria-label="End Date" />
                </div>

                {{-- Min/Max Equity Inputs in a compact popover/dropdown --}}
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-xs btn-outline gap-1 {{ ($minEquity !== null || $maxEquity !== null) ? 'btn-primary' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Equity Range
                    </div>
                    <div tabindex="0" class="dropdown-content z-30 menu p-4 shadow-2xl bg-base-100 border border-base-300 rounded-box w-64 space-y-3 mt-1">
                        <div class="font-bold text-xs uppercase tracking-wider text-base-content/70 pb-1 border-b border-base-200">Equity Range Filter</div>
                        <div class="space-y-1">
                            <label class="label-text text-[11px] font-semibold">Min Equity</label>
                            <input type="number" step="any" name="min_equity" value="{{ $minEquity ?? '' }}" placeholder="e.g. 10000" class="input input-xs input-bordered w-full" />
                        </div>
                        <div class="space-y-1">
                            <label class="label-text text-[11px] font-semibold">Max Equity</label>
                            <input type="number" step="any" name="max_equity" value="{{ $maxEquity ?? '' }}" placeholder="e.g. 50000" class="input input-xs input-bordered w-full" />
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="btn btn-xs btn-primary w-full font-semibold">Apply Range</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-xs btn-primary font-semibold">Filter</button>
                @if($hasActiveBalanceFilters)
                    <a href="{{ route('balances.index') }}" class="btn btn-xs btn-ghost text-error" title="Clear All Filters">✕</a>
                @endif
            </form>
        </div>

        @if($balances->isNotEmpty())
            <x-table>
                <x-slot:header>
                    <th class="py-4 uppercase text-center">Date</th>
                    <th class="py-4 uppercase">Wallet Balance</th>
                    <th class="py-4 uppercase">Total Equity</th>
                    <th class="py-4 uppercase">Realised Pnl (Cum)</th>
                    <th class="py-4 uppercase">Actions</th>
                </x-slot:header>
                    <tbody id="balances-table-body">
                        @foreach ($balances as $balance)
                            <x-table.row onclick="document.getElementById('modal_{{ $balance->id }}').showModal()" class="cursor-pointer">
                                <td class="font-medium text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{ \Carbon\Carbon::parse($balance->local_date)->format('M d, Y') ?? $balance->local_date }}
                                        @if($balance->is_demo)
                                            <span class="badge badge-warning badge-xs font-bold uppercase py-2">Demo</span>
                                        @endif
                                        <span @class([
                                            'badge badge-xs font-bold uppercase py-2',
                                            'badge-secondary' => $balance->market === 'crypto',
                                            'badge-accent' => $balance->market === 'pse',
                                        ])>{{ $balance->market }}</span>
                                    </div>
                                </td>                                
                                <td>
                                    @currency($balance->wallet_balance, $balance->market)
                                </td>
                                <td>
                                    @currency($balance->total_equity, $balance->market)
                                </td>
                                <td>
                                    <span @class([
                                        'font-bold',
                                        'text-green-500' => $balance->cum_realised_pnl > 0,
                                        'text-red-500' => $balance->cum_realised_pnl < 0
                                    ])>@currency($balance->cum_realised_pnl, $balance->market)</span>
                                </td>
                                <td class="text-right">
                                    <div class="flex gap-4 items-center justify-center">
                                        <!-- Edit Icon -->
                                        <button type="button"
                                            onclick="event.stopPropagation(); document.getElementById('edit_modal_{{ $balance->id }}').showModal()"
                                            class="text-blue-500 hover:text-blue-700 transition cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>
                                        <!-- Trash Icon -->
                                        <button type="button"
                                            onclick="event.stopPropagation(); document.getElementById('delete_confirmation_modal_{{ $balance->id }}').showModal()"
                                            class="text-red-500 hover:text-red-700 transition cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </x-table.row>
                        @endforeach
                    </tbody>
            </x-table>

            <div class="mt-4">
                {{ $balances->links() }}
            </div>

            <script>
                if (window.Echo) {
                    window.Echo.private("App.Models.User." + @js(auth()->id()))
                        .listen('.NewTradesFetched', (e) => {
                            console.log('Real-time balance update:', e);
                            if (window.showToast) {
                                window.showToast(e.message || 'Balances updated!', 'success');
                            }
                            refreshBalances();
                        });
                }

                async function refreshBalances() {
                    try {
                        const response = await fetch("{{ route('balances.index') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        
                        const tbody = document.getElementById('balances-table-body');
                        if (tbody && data.balances && data.balances.data) {
                            tbody.innerHTML = data.balances.data.map(balance => {
                                const pnlClass = balance.cum_realised_pnl > 0 ? 'text-green-500' : (balance.cum_realised_pnl < 0 ? 'text-red-500' : '');
                                const marketBadgeClass = balance.market === 'crypto' ? 'badge-secondary' : 'badge-accent';
                                
                                return `
                                    <tr class="hover cursor-pointer" onclick="document.getElementById('modal_${balance.id}').showModal()">
                                        <td class="font-medium text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                ${balance.local_date}
                                                ${balance.is_demo ? '<span class="badge badge-warning badge-xs font-bold uppercase py-2">Demo</span>' : ''}
                                                <span class="badge badge-xs font-bold uppercase py-2 ${marketBadgeClass}">${balance.market}</span>
                                            </div>
                                        </td>
                                        <td>${balance.formatted_wallet}</td>
                                        <td>${balance.formatted_equity}</td>
                                        <td><span class="font-bold ${pnlClass}">${balance.formatted_pnl}</span></td>
                                        <td class="text-right">
                                            <div class="flex gap-4 items-center justify-center">
                                                <button type="button" onclick="event.stopPropagation(); document.getElementById('edit_modal_${balance.id}').showModal()" class="text-blue-500 hover:text-blue-700 transition cursor-pointer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                                </button>
                                                <button type="button" onclick="event.stopPropagation(); document.getElementById('delete_confirmation_modal_${balance.id}').showModal()" class="text-red-500 hover:text-red-700 transition cursor-pointer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            }).join('');
                        }
                    } catch (error) {
                        console.error('Error refreshing balances:', error);
                    }
                }
            </script>
        @else
            <div class="mt-8">
                <div class="bg-base-200 border-2 border-dashed border-base-300 rounded-xl p-12 text-center shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-12 h-12 mx-auto text-base-content/30 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>

                    <h3 class="font-bold text-xl text-base-content mb-2">No balance history yet</h3>
                    <p class="text-base-content/60 mb-6 max-w-sm mx-auto">Add your first balance entry to start monitoring your wallet and equity.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('balances.create') }}" class="btn btn-primary px-8">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Entry
                        </a>
                        <button type="button" onclick="document.getElementById('balance_import_modal').showModal()" class="btn btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            Import CSV
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @foreach ($balances as $balance)
            <!-- Delete Confirmation Modal -->
            <dialog id="delete_confirmation_modal_{{ $balance->id }}" class="modal">
                <div class="modal-box">
                    <h3 class="text-lg font-bold">Confirm Deletion</h3>
                    <p class="py-4">Are you sure you want to delete this balance entry? This action cannot be undone.</p>
                    <div class="modal-action">
                        <!-- method="dialog" closes the modal without a page refresh -->
                        <form method="dialog">
                            <button class="btn">Cancel</button>
                        </form>
                        <form action="{{ route('balances.destroy', $balance->id) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-error" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
            <!-- Show Info -->
            <dialog id="modal_{{ $balance->id }}" class="modal">
                <div class="modal-box">
                    <h3 class="text-lg font-bold">Balance on {{ $balance->local_date }}</h3>
                    <div class="py-4 space-y-2 text-left">
                        <p><strong>Wallet Balance:</strong> @currency($balance->wallet_balance, $balance->market)</p>
                        <p><strong>Total Equity:</strong> @currency($balance->total_equity, $balance->market)</p>
                        <p><strong>Realised Pnl (Cum):</strong>
                            <span @class([
                                'font-bold',
                                'text-green-500' => $balance->cum_realised_pnl > 0,
                                'text-red-500' => $balance->cum_realised_pnl < 0
                            ])>@currency($balance->cum_realised_pnl, $balance->market)</span>
                        </p>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>

            <!-- Edit Modal -->
            <dialog id="edit_modal_{{ $balance->id }}" class="modal text-left">
                <div class="modal-box w-11/12 max-w-4xl">
                    <h3 class="text-lg font-bold">Edit Balance Entry</h3>
                    <form action="{{ route('balances.update', $balance->id) }}" method="post" class="mt-4" id="form">
                        @csrf
                        @method('PUT')
                        <x-errors />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                        Date & Time</legend>
                                    <input type="datetime-local" class="input w-full" name="date"
                                        value="{{ \Carbon\Carbon::parse($balance->date)->format('Y-m-d\TH:i') }}"
                                        required />
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                        Wallet Balance</legend>
                                    <input type="number" step="any" class="input w-full" name="wallet_balance"
                                        value="{{ $balance->wallet_balance }}" required />
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                        Total Equity</legend>
                                    <input type="number" step="any" class="input w-full" name="total_equity"
                                        value="{{ $balance->total_equity }}" required />
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                        Cumulative Realized PnL</legend>
                                    <input type="number" step="any" class="input w-full" name="cum_realised_pnl"
                                        value="{{ $balance->cum_realised_pnl }}" required />
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                        Market</legend>
                                    <select class="select w-full" name="market">
                                        <option value="crypto" @selected($balance->market === 'crypto')>Crypto</option>
                                        <option value="pse" @selected($balance->market === 'pse')>PSE</option>
                                    </select>
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                        Account Mode</legend>
                                    <label class="label cursor-pointer justify-start gap-4 h-full">
                                        <input type="hidden" name="is_demo" value="0">
                                        <input type="checkbox" name="is_demo" value="1" class="checkbox checkbox-warning" {{ $balance->is_demo ? 'checked' : '' }} />
                                        <span class="label-text font-bold text-warning uppercase">Demo Entry</span>
                                    </label>
                                </fieldset>
                            </div>
                        </div>

                        <div class="modal-action">
                            <button type="button" class="btn"
                                onclick="document.getElementById('edit_modal_{{ $balance->id }}').close()">Cancel</button>
                            <button class="btn btn-primary" type="submit">Save Changes</button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        @endforeach

        <!-- Import Balances Modal -->
        <dialog id="balance_import_modal" class="modal">
            <div class="modal-box max-w-3xl max-h-[90vh] overflow-y-auto space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-base-300">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            Import Balance History from CSV
                        </h3>
                        <p class="text-xs text-base-content/60 mt-0.5">Upload a CSV or text spreadsheet to bulk-import your daily balance logs.</p>
                    </div>
                    <form method="dialog">
                        <button class="btn btn-sm btn-circle btn-ghost" aria-label="Close modal">✕</button>
                    </form>
                </div>

                <!-- Template Download Card -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-xl bg-primary/5 border border-primary/20">
                    <div>
                        <p class="text-sm font-semibold text-primary">Need the official CSV format?</p>
                        <p class="text-xs text-base-content/70">Download a pre-formatted template with example rows for Crypto and PSE balances.</p>
                    </div>
                    <a href="{{ route('balances.template') }}" class="btn btn-sm btn-primary shrink-0 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Sample CSV
                    </a>
                </div>

                <!-- Column Reference Table -->
                <div class="border border-base-300 rounded-lg overflow-hidden bg-base-100 text-xs">
                    <div class="bg-base-200 px-3 py-2 font-bold uppercase tracking-wider text-base-content/70 flex items-center justify-between">
                        <span>CSV Column Guide</span>
                        <span class="badge badge-xs badge-ghost">Required & Optional</span>
                    </div>
                    <table class="table table-xs">
                        <thead>
                            <tr class="bg-base-300/40 text-base-content font-bold">
                                <th>Column Header</th>
                                <th>Status</th>
                                <th>Example</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200">
                            <tr class="hover:bg-base-200/50">
                                <td class="font-mono font-bold text-primary">date</td>
                                <td><span class="badge badge-primary badge-xs">Required</span></td>
                                <td class="font-mono text-base-content/80">2026-03-01</td>
                                <td class="text-base-content/70">Date of the balance entry (YYYY-MM-DD)</td>
                            </tr>
                            <tr class="hover:bg-base-200/50">
                                <td class="font-mono font-bold text-primary">wallet_balance</td>
                                <td><span class="badge badge-primary badge-xs">Required</span></td>
                                <td class="font-mono text-base-content/80">10000.00</td>
                                <td class="text-base-content/70">Wallet cash / available balance</td>
                            </tr>
                            <tr class="hover:bg-base-200/50">
                                <td class="font-mono font-bold text-primary">total_equity</td>
                                <td><span class="badge badge-primary badge-xs">Required</span></td>
                                <td class="font-mono text-base-content/80">10450.00</td>
                                <td class="text-base-content/70">Total net portfolio value including open positions</td>
                            </tr>
                            <tr class="hover:bg-base-200/50">
                                <td class="font-mono font-bold text-primary">cum_realised_pnl</td>
                                <td><span class="badge badge-primary badge-xs">Required</span></td>
                                <td class="font-mono text-base-content/80">450.00</td>
                                <td class="text-base-content/70">All-time cumulative realized net profit/loss</td>
                            </tr>
                            <tr class="hover:bg-base-200/50">
                                <td class="font-mono text-base-content">market</td>
                                <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                                <td class="font-mono text-base-content/80">crypto / pse</td>
                                <td class="text-base-content/70">Market type (default: <code>crypto</code>)</td>
                            </tr>
                            <tr class="hover:bg-base-200/50">
                                <td class="font-mono text-base-content">is_demo</td>
                                <td><span class="badge badge-ghost badge-xs">Optional</span></td>
                                <td class="font-mono text-base-content/80">0 / 1</td>
                                <td class="text-base-content/70"><code>1</code> for paper/demo entry, <code>0</code> for real (default: <code>0</code>)</td>
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
                                    <th>date</th>
                                    <th>market</th>
                                    <th>wallet_balance</th>
                                    <th>total_equity</th>
                                    <th>cum_realised_pnl</th>
                                    <th>is_demo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200">
                                <tr class="hover:bg-base-200/40">
                                    <td class="text-center font-bold bg-base-200/60 text-base-content/50">1</td>
                                    <td class="text-primary font-bold">2026-02-28</td>
                                    <td>crypto</td>
                                    <td>10000.00</td>
                                    <td>10450.00</td>
                                    <td class="text-success font-bold">450.00</td>
                                    <td>0</td>
                                </tr>
                                <tr class="hover:bg-base-200/40">
                                    <td class="text-center font-bold bg-base-200/60 text-base-content/50">2</td>
                                    <td class="text-primary font-bold">2026-03-01</td>
                                    <td>pse</td>
                                    <td>150000.00</td>
                                    <td>158500.00</td>
                                    <td class="text-success font-bold">8500.00</td>
                                    <td>0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Upload Form with Drag-and-Drop Area -->
                <form action="{{ route('balances.import') }}" method="POST" enctype="multipart/form-data" id="balance-import-form" class="space-y-4">
                    @csrf
                    
                    <div 
                        id="balance-drop-zone"
                        class="border-2 border-dashed border-base-300 hover:border-primary/60 rounded-xl p-8 text-center transition-all bg-base-200/40 hover:bg-base-200 cursor-pointer flex flex-col items-center justify-center gap-3 relative group"
                    >
                        <input 
                            type="file" 
                            name="file" 
                            id="balance-file-input" 
                            accept=".csv, text/csv, text/plain" 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            required
                        />

                        <div id="balance-drop-zone-prompt" class="flex flex-col items-center gap-2 pointer-events-none">
                            <div class="p-3 bg-primary/10 rounded-full text-primary group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-base-content">
                                    <span class="text-primary hover:underline">Click to browse</span> or drag and drop your CSV file here
                                </p>
                                <p class="text-xs text-base-content/60 mt-1">Supports UTF-8 encoded .csv or .txt (Max 10MB)</p>
                            </div>
                        </div>

                        <!-- Selected File Status Preview -->
                        <div id="balance-selected-file-info" class="hidden w-full flex items-center justify-between p-3 bg-base-100 rounded-lg border border-base-300">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="p-2 bg-success/10 text-success rounded-lg shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div class="text-left truncate">
                                    <p id="balance-file-name" class="text-sm font-semibold truncate text-base-content">filename.csv</p>
                                    <p id="balance-file-size" class="text-xs text-base-content/60">0 KB</p>
                                </div>
                            </div>
                            <button type="button" id="balance-remove-file-btn" class="btn btn-ghost btn-xs btn-circle text-error" title="Remove file">✕</button>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" class="btn" onclick="document.getElementById('balance_import_modal').close()">Cancel</button>
                        <button type="submit" id="balance-submit-import-btn" class="btn btn-primary gap-2" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            Start Import
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
                const dropZone = document.getElementById('balance-drop-zone');
                const fileInput = document.getElementById('balance-file-input');
                const promptEl = document.getElementById('balance-drop-zone-prompt');
                const infoEl = document.getElementById('balance-selected-file-info');
                const fileNameEl = document.getElementById('balance-file-name');
                const fileSizeEl = document.getElementById('balance-file-size');
                const removeBtn = document.getElementById('balance-remove-file-btn');
                const submitBtn = document.getElementById('balance-submit-import-btn');
                const formEl = document.getElementById('balance-import-form');

                if (!dropZone || !fileInput) return;

                function formatBytes(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }

                function updateFile(file) {
                    if (file) {
                        fileNameEl.textContent = file.name;
                        fileSizeEl.textContent = formatBytes(file.size);
                        promptEl.classList.add('hidden');
                        infoEl.classList.remove('hidden');
                        submitBtn.disabled = false;
                    } else {
                        fileInput.value = '';
                        promptEl.classList.remove('hidden');
                        infoEl.classList.add('hidden');
                        submitBtn.disabled = true;
                    }
                }

                fileInput.addEventListener('change', function (e) {
                    const file = e.target.files && e.target.files[0];
                    updateFile(file);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.add('border-primary', 'bg-primary/5');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.remove('border-primary', 'bg-primary/5');
                    }, false);
                });

                dropZone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const file = dt && dt.files && dt.files[0];
                    if (file) {
                        fileInput.files = dt.files;
                        updateFile(file);
                    }
                });

                if (removeBtn) {
                    removeBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        updateFile(null);
                    });
                }

                if (formEl) {
                    formEl.addEventListener('submit', function () {
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Importing...';
                        }
                    });
                }
            })();
        </script>
    </div>
    @include('components.form-dirty-state-check')
</x-layouts.app>