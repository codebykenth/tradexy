<x-layouts.app>
    <div class="max-w-7xl mx-auto px-6 space-y-4 mb-8">
        <div class="relative h-12 mb-4">
            <a href="{{ route('strategies.create') }}" class="btn btn-primary absolute left-0 top-0 h-full">New
                Strategy</a>
        </div>
        @if($strategies)
            <div class="border border-gray-300 rounded-lg overflow-x-auto">
                <table class="w-full">
                    <tr
                        class="border-b border-gray-300 bg-gray-100 h-10 [&>th:first-child]:pl-4 [&>th:last-child]:pr-4 uppercase">
                        <th>Set-up</th>
                        <th>Net P/L</th>
                        <th>No. of Trades</th>
                        <th>Total Win Amt.</th>
                        <th>Total Loss Amt.</th>
                        <th>Avg Win</th>
                        <th>Avg Loss</th>
                        <th>Hit Ratio (%)</th>
                        <th>Edge Ratio (x)</th>
                        <th>Action</th>
                    </tr>
                    @forelse ($strategies as $strategy)
                        <tr class="border-b border-gray-300 odd:bg-gray-100 even:bg-white hover:bg-gray-200 transition-colors text-center h-12 [&>th:first-child]:pl-4 [&>td:last-child]:pr-4 cursor-pointer"
                            onclick="document.getElementById('modal_{{ $strategy->id }}').showModal()">
                            <td>
                                {{ $strategy->name }}
                            </td>
                            <td @class([
                                'text-green-400' => $strategy->net_pnl > 0,
                                'text-red-400' => $strategy->net_pnl < 0
                            ])>
                                {{ $strategy->net_pnl }}
                            </td>
                            <td>
                                {{ $strategy->trades_count }}

                            </td>
                            <td>
                                {{ number_format($strategy->total_win_amount, 2) }}
                            </td>
                            <td>
                                {{ number_format($strategy->total_loss_amount, 2) }}
                            </td>
                            <td>
                                {{ number_format($strategy->avg_win, 2) }}

                            </td>
                            <td>
                                {{ number_format($strategy->avg_loss, 2) }}

                            </td>
                            <td @class([
                                'text-black' => $strategy->hit_ratio == 0,
                                'text-orange-400' => $strategy->hit_ratio == 50,
                                'text-green-400' => $strategy->hit_ratio > 50,
                                'text-red-400' => $strategy->hit_ratio < 50 && $strategy->hit_ratio != 0,
                            ])>
                                {{ number_format($strategy->hit_ratio, 2) }}

                            </td>
                            <td @class([
                                'text-black' => $strategy->edge_ratio == 0,
                                'text-red-400' => $strategy->edge_ratio > 0 && $strategy->edge_ratio < 1,
                                'text-orange-400' => $strategy->edge_ratio == 1,
                                'text-green-400' => $strategy->edge_ratio > 1 && $strategy->edge_ratio <= 2,
                                'text-emerald-500 font-bold' => $strategy->edge_ratio > 2,
                            ])>
                                {{ number_format($strategy->edge_ratio, 2) }}

                            </td>
                            <td>
                                <div class="flex gap-4 items-center justify-center">
                                    <!-- Edit Icon -->
                                    <button type="button"
                                        onclick="event.stopPropagation(); document.getElementById('edit_modal_{{ $strategy->id }}').showModal()"
                                        class="text-blue-500 hover:text-blue-700 transition cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>
                                    <!-- Trash Icon -->
                                    <button type="button"
                                        onclick="event.stopPropagation(); document.getElementById('delete_confirmation_modal_{{ $strategy->id }}').showModal()"
                                        class="text-red-500 hover:text-red-700 transition cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <p class="p-8 text-center">No strategy history yet.</p>
                    @endforelse
                </table>
            </div>
        @else
            <div class="text-lg text-center">No strategy history yet.</div>
        @endif

        @foreach ($strategies as $strategy)
            <!-- Delete Confirmation Modal -->
            <dialog id="delete_confirmation_modal_{{ $strategy->id }}" class="modal">
                <div class="modal-box">
                    <h3 class="text-lg font-bold">Confirm Deletion</h3>
                    <p class="py-4">Are you sure you want to delete this strategy entry? This action cannot be undone.</p>
                    <div class="modal-action">
                        <!-- method="dialog" closes the modal without a page refresh -->
                        <form method="dialog">
                            <button class="btn">Cancel</button>
                        </form>
                        <form action="{{ route('strategies.destroy', $strategy->id) }}" method="post">
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
            <!-- <dialog id="modal_{{ $strategy->id }}" class="modal">
                                                        <div class="modal-box">
                                                            <h3 class="text-lg font-bold">strategy on {{ $strategy->local_date }}</h3>
                                                            <div class="py-4 space-y-2 text-left">
                                                                <p><strong>Wallet strategy:</strong> {{ $strategy->wallet_strategy }}</p>
                                                                <p><strong>Total Equity:</strong> {{ $strategy->total_equity }}</p>
                                                                <p><strong>Realised Pnl (Cum):</strong>
                                                                    <span @class([
                                                                        'font-bold',
                                                                        'text-green-500' => $strategy->cum_realised_pnl > 0,
                                                                        'text-red-500' => $strategy->cum_realised_pnl < 0
                                                                    ])>{{ $strategy->cum_realised_pnl }}</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <form method="dialog" class="modal-backdrop">
                                                            <button>close</button>
                                                        </form>
                                                    </dialog> -->

            <!-- Edit Modal -->
            <!-- <dialog id="edit_modal_{{ $strategy->id }}" class="modal text-left">
                                                        <div class="modal-box w-11/12 max-w-4xl">
                                                            <h3 class="text-lg font-bold">Edit strategy Entry</h3>
                                                            <form action="{{ route('strategies.update', $strategy->id) }}" method="post" class="mt-4" id="form">
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
                                                                                value="{{ \Carbon\Carbon::parse($strategy->date)->format('Y-m-d\TH:i') }}"
                                                                                required />
                                                                        </fieldset>
                                                                    </div>
                                                                    <div>
                                                                        <fieldset class="fieldset w-full">
                                                                            <legend
                                                                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                                                                Wallet strategy</legend>
                                                                            <input type="number" step="any" class="input w-full" name="wallet_strategy"
                                                                                value="{{ $strategy->wallet_strategy }}" required />
                                                                        </fieldset>
                                                                    </div>
                                                                    <div>
                                                                        <fieldset class="fieldset w-full">
                                                                            <legend
                                                                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                                                                Total Equity</legend>
                                                                            <input type="number" step="any" class="input w-full" name="total_equity"
                                                                                value="{{ $strategy->total_equity }}" required />
                                                                        </fieldset>
                                                                    </div>
                                                                    <div>
                                                                        <fieldset class="fieldset w-full">
                                                                            <legend
                                                                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                                                                Cumulative Realized PnL</legend>
                                                                            <input type="number" step="any" class="input w-full" name="cum_realised_pnl"
                                                                                value="{{ $strategy->cum_realised_pnl }}" required />
                                                                        </fieldset>
                                                                    </div>
                                                                </div>

                                                                <div class="modal-action">
                                                                    <button type="button" class="btn"
                                                                        onclick="document.getElementById('edit_modal_{{ $strategy->id }}').close()">Cancel</button>
                                                                    <button class="btn btn-primary" type="submit">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <form method="dialog" class="modal-backdrop">
                                                            <button>close</button>
                                                        </form>
                                                    </dialog> -->
        @endforeach
    </div>
</x-layouts.app>
@include('components.form-dirty-state-check')