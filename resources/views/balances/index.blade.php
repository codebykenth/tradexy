<x-layouts.app title="Balances - Tradexy">
    <div class="max-w-7xl mx-auto px-6 space-y-4 mb-8">
        <x-page-title title="Balance & Equity History" />
        @if($balances->isNotEmpty())
            <div class="relative h-12 mb-4">
                <a href="{{ route('balances.create') }}" class="btn btn-primary absolute left-0 top-0 h-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Entry
                </a>
            </div>
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
                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-12 h-12 mx-auto text-gray-400 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>

                    <h3 class="font-bold text-lg text-gray-900 mb-2">No balance history yet</h3>
                    <p class="text-gray-500 mb-4">Add your first balance entry to start monitoring your wallet and equity.
                    </p>
                    <a href="{{ route('balances.create') }}" class="btn btn-primary">
                        + Add Entry
                    </a>
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
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                        Date & Time</legend>
                                    <input type="datetime-local" class="input w-full" name="date"
                                        value="{{ \Carbon\Carbon::parse($balance->date)->format('Y-m-d\TH:i') }}"
                                        required />
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                        Wallet Balance</legend>
                                    <input type="number" step="any" class="input w-full" name="wallet_balance"
                                        value="{{ $balance->wallet_balance }}" required />
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                        Total Equity</legend>
                                    <input type="number" step="any" class="input w-full" name="total_equity"
                                        value="{{ $balance->total_equity }}" required />
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                        Cumulative Realized PnL</legend>
                                    <input type="number" step="any" class="input w-full" name="cum_realised_pnl"
                                        value="{{ $balance->cum_realised_pnl }}" required />
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="fieldset w-full">
                                    <legend
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                                        class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
    </div>
</x-layouts.app>

@include('components.form-dirty-state-check')