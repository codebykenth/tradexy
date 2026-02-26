<x-layouts.app>
    <div class="max-w-7xl mx-auto px-6 space-y-4 mb-8">
        <div class="relative h-12 mb-4">
            <a href="{{ route('balances.create') }}" class="btn btn-primary absolute left-0 top-0 h-full">Add
                Entry</a>
        </div>
        @if($balances)
            <div class="border border-gray-300 rounded-lg overflow-x-auto">
                <table class="w-full">
                    <tr
                        class="border-b border-gray-300 bg-gray-100 h-10 [&>th:first-child]:pl-4 [&>th:last-child]:pr-4 uppercase">
                        <th>Date</th>
                        <th>Wallet Balance</th>
                        <th>Total Equity</th>
                        <th>Realised Pnl (Cum)</th>
                        <th>Actions</th>
                    </tr>
                    @forelse ($balances as $balance)
                        <tr class="border-b border-gray-300 odd:bg-gray-100 even:bg-white hover:bg-gray-200 transition-colors text-center h-12 [&>th:first-child]:pl-4 [&>td:last-child]:pr-4 cursor-pointer"
                            onclick="document.getElementById('modal_{{ $balance->id }}').showModal()">
                            <td>
                                {{ $balance->local_date }}
                            </td>
                            <td>
                                {{ $balance->wallet_balance }}
                            </td>
                            <td>
                                {{ $balance->total_equity }}
                            </td>
                            <td>
                                <span @class([
                                    'font-bold',
                                    'text-green-400' => $balance->cum_realised_pnl > 0,
                                    'text-red-400' => $balance->cum_realised_pnl < 0
                                ])>{{ $balance->cum_realised_pnl }}</span>
                            </td>
                            <td>
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
                        </tr>
                    @empty
                        <p class="p-8 text-center">No balance history yet.</p>
                    @endforelse
                </table>
            </div>
        @else
            <div class="text-lg text-center">No balance history yet.</div>
        @endif
        {{ $balances->links() }}

        @foreach ($balances as $balance)
            <!-- Delete Confirmation Modal -->
            <dialog id="delete_confirmation_modal_{{ $balance->id }}" class="modal">
                <div class="modal-box">
                    <h2>Are you sure you want to delete this balance entry?</h2>
                    <form action="{{ route('balances.destroy', $balance->id) }}" method="post" class="flex flex-end">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-error" type="submit">Delete</button>
                    </form>
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
                        <p><strong>Wallet Balance:</strong> {{ $balance->wallet_balance }}</p>
                        <p><strong>Total Equity:</strong> {{ $balance->total_equity }}</p>
                        <p><strong>Realised Pnl (Cum):</strong>
                            <span @class([
                                'font-bold',
                                'text-green-500' => $balance->cum_realised_pnl > 0,
                                'text-red-500' => $balance->cum_realised_pnl < 0
                            ])>{{ $balance->cum_realised_pnl }}</span>
                        </p>
                    </div>
                </div>
                </form>
            </dialog>

            <!-- Edit Modal -->
            <dialog id="edit_modal_{{ $balance->id }}" class="modal text-left">
                <div class="modal-box w-11/12 max-w-4xl">
                    <h3 class="text-lg font-bold">Edit Balance Entry</h3>
                    <form action="{{ route('balances.update', $balance->id) }}" method="post" class="mt-4">
                        @csrf
                        @method('PUT')
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