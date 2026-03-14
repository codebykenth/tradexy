<x-layouts.app title="Trade Logs - Tradexy">
    <div class="w-full">
        <div class="max-w-7xl mx-auto px-6 space-y-4 mb-8">
            <x-page-title title="Logs" subtitle="List of all your trades" />
            @if($ownedTrades->isNotEmpty())
                <div class="relative h-12 mb-4">
                    <a href="{{ route('trades.create') }}" class="btn btn-primary absolute left-0 top-0 h-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Trade
                    </a>

                    <div
                        class="bulk-action-container absolute right-0 top-0 h-full flex items-center gap-4 hidden bg-gray-100 p-2 rounded-lg border border-gray-300">
                        <span class="text-sm font-semibold text-gray-600 px-2">Bulk Actions:</span>
                        <select class="select select-sm border-gray-300" name="timeframe" id="bulk-timeframe">
                            <option value="">Timeframe...</option>
                            <option>1m</option>
                            <option>5m</option>
                            <option>15m</option>
                            <option>30m</option>
                            <option>1hr</option>
                            <option>4hr</option>
                            <option>1d</option>
                        </select>
                        <select class="select select-sm border-gray-300" name="strategy_id" id="bulk-strategy">
                            <option value="">Strategy...</option>
                            @foreach($strategies as $strategy)
                                <option value="{{ $strategy->id }}">{{ $strategy->name }}</option>
                            @endforeach
                        </select>

                        <button class="btn btn-sm btn-primary" id="apply-bulk">Update</button>
                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                        <!-- Delete Button -->
                        <button class="btn btn-sm btn-square btn-error btn-outline" id="bulk-delete" aria-label="Delete Selected">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>
                <x-table>
                    <x-slot:header>
                        <th>
                            <label>
                                <input type="checkbox" class="all-trade-checkbox size-4" />
                            </label>
                        </th>
                        <th>Date</th>
                        <th class="text-center">Symbol</th>
                        <th>Duration</th>
                        <th>Qty</th>
                        <th>Pnl</th>
                        <th>Chart</th>
                        <th>AI</th>
                    </x-slot:header>
                    @foreach ($ownedTrades as $ownedTrade)
                        <x-table.row onclick="window.location='/trades/{{ $ownedTrade->id }}'">
                            <th onclick="event.stopPropagation()">
                                <label>
                                    <input type="checkbox" class="trade-checkbox size-4" value="{{ $ownedTrade->id }}" />
                                </label>
                            </th>
                            <td class="font-medium">
                                {{ \Carbon\Carbon::parse($ownedTrade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-2">
                                    {{ $ownedTrade->symbol }}
                                    <span
                                        class="badge badge-outline badge-xs uppercase">{{ $ownedTrade->market ?? 'crypto' }}</span>
                                    @if($ownedTrade->is_demo)
                                        <span class="badge badge-warning badge-xs uppercase">Demo</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                {{ $ownedTrade->duration }}
                            </td>
                            <td>
                                {{ strpos((string) $ownedTrade->quantity, '.') !== false ? rtrim(rtrim((string) $ownedTrade->quantity, '0'), '.') : $ownedTrade->quantity }}
                            </td>
                            <td>
                                <span @class([
                                    'font-bold',
                                    'text-green-400' => $ownedTrade->total_pnl > 0,
                                    'text-red-400' => $ownedTrade->total_pnl < 0
                                ])>{{ number_format($ownedTrade->total_pnl, 2) }}</span>
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($ownedTrade->chart_picture)
                                    <button class="btn btn-ghost btn-sm" onclick="modal_chart_{{ $ownedTrade->id }}.showModal()">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                        </svg>
                                    </button>
                                    <dialog id="modal_chart_{{ $ownedTrade->id }}" class="modal">
                                        <div class="modal-box w-11/12 max-w-5xl cursor-auto">
                                            <h3 class="text-lg font-bold">Chart</h3>
                                            <img src="{{ $ownedTrade->direct_chart_url ?? "" }}" alt="">
                                        </div>
                                        <form method="dialog" class="modal-backdrop">
                                            <button>close</button>
                                        </form>
                                    </dialog>
                                @endif
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($ownedTrade->ai_analysis)
                                                <button class="btn btn-ghost btn-sm" onclick="modal_ai_{{ $ownedTrade->id }}.showModal()">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                        stroke="currentColor" class="size-5 text-indigo-500">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.456-2.454L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.454 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                                    </svg>
                                                </button>
                                                <dialog id="modal_ai_{{ $ownedTrade->id }}" class="modal">
                                                    <div class="modal-box w-11/12 max-w-4xl cursor-auto">
                                                        <h3 class="text-2xl font-bold mb-6 text-indigo-700">Trade Analysis</h3>

                                                        <!-- AI Content Container with dynamic styling for markdown elements -->
                                                        <div
                                                            class="text-left text-gray-700 text-[15px] leading-relaxed [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-gray-900 [&_h3]:mt-8 [&_h3]:mb-4 [&_h3]:border-b [&_h3]:border-gray-200 [&_h3]:pb-2 [&_h3]:flex [&_h3]:items-center [&_h3]:gap-2 [&_p]:mb-4 [&_p]:text-gray-600 [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-6 [&_ul]:space-y-2 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-6 [&_ol]:space-y-2 [&_li]:text-gray-700 [&_strong]:font-bold [&_strong]:text-gray-900 max-h-[65vh] overflow-y-auto pr-4 scrollbar-thin">

                                                            {!! \Illuminate\Support\Str::markdown($ownedTrade->ai_analysis, [
                                        'html_input' => 'strip',
                                        'allow_unsafe_links' => false
                                    ]) !!}

                                                        </div>

                                                        <div class="modal-action">
                                                            <form method="dialog">
                                                                <button class="btn">Close</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <form method="dialog" class="modal-backdrop">
                                                        <button>close</button>
                                                    </form>
                                                </dialog>
                                @endif
                            </td>
                        </x-table.row>
                    @endforeach
                </x-table>

                <div class="mt-4">
                    {{ $ownedTrades->links() }}
                </div>
            @else
                <div class="mt-8">
                    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-12 h-12 mx-auto text-gray-400 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m-1.5 4.5h.008v.008H6.75V11.25Zm0 3h.008v.008H6.75V14.25Zm0 3h.008v.008H6.75V17.25ZM12 11.25h.008v.008H12V11.25Zm0 3h.008v.008H12V14.25Zm0 3h.008v.008H12V17.25ZM17.25 11.25h.008v.008h-.008V11.25Zm0 3h.008v.008h-.008V14.25Z" />
                        </svg>

                        <h3 class="font-bold text-lg text-gray-900 mb-2">No trades added yet</h3>
                        <p class="text-gray-500 mb-4">Add your first trade to start analyzing your market edge.</p>
                        <a href="{{ route('trades.create') }}" class="btn btn-primary">
                            + Add Trade
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>

</x-layouts.app>

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
                <button type="button" onclick="executeBulkDelete()" class="btn btn-error flex-2 font-black uppercase tracking-widest text-[10px] px-8">Confirm Delete</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    let bulkBtn = document.getElementById('apply-bulk')
    let allTradesBtn = document.querySelector('.all-trade-checkbox')
    let singleTradeCheckbox = document.querySelectorAll('.trade-checkbox')
    let bulkContainer = document.querySelector('.bulk-action-container')

    // Select a checkbox then show apply button dynamically (toggle)
    for (let i = 0; i < singleTradeCheckbox.length; i++) {
        singleTradeCheckbox[i].addEventListener('click', function () {

            let checkedCount = document.querySelectorAll('.trade-checkbox:checked').length
            console.log(checkedCount)

            if (checkedCount == 0) {
                bulkContainer.classList.add('hidden')
                allTradesBtn.checked = false
            } else if (checkedCount == 10) {
                allTradesBtn.checked = true
            } else {
                bulkContainer.classList.remove('hidden')
                allTradesBtn.checked = false
            }
        })
    }

    function executeBulkDelete() {
        submitBulkAction('delete');
    }

    function performBulkAction(action) {
        let checkedTrades = document.querySelectorAll('.trade-checkbox:checked');
        let tradeIds = Array.from(checkedTrades).map(cb => cb.value);

        if (tradeIds.length === 0) return;

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

    function selectAllTrades() {
        let isChecked = allTradesBtn.checked
        let allTradesCheckbox = document.querySelectorAll('.trade-checkbox')

        for (let i = 0; i < allTradesCheckbox.length; i++) {
            allTradesCheckbox[i].checked = isChecked
        }

        let checkedCount = document.querySelectorAll('.trade-checkbox:checked').length

        if (checkedCount == 0) {
            bulkContainer.classList.add('hidden')
        } else {
            bulkContainer.classList.remove('hidden')
        }
    }

    bulkBtn.addEventListener('click', () => performBulkAction('update'));
    document.getElementById('bulk-delete').addEventListener('click', () => performBulkAction('delete'));
    allTradesBtn.addEventListener('click', selectAllTrades)
</script>