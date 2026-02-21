<x-layouts.app>
    <div class="w-full">
        <div class="max-w-7xl mx-auto px-6 space-y-4">
            <button>Add Trade</button>

            <div class="border border-gray-300 rounded-lg overflow-x-auto">
                <table class="w-full ">
                    <tr class="border-b border-gray-300 bg-gray-100 h-8">
                        <th>
                            <label>
                                <input type="checkbox" className="checkbox" />
                            </label>
                        </th>
                        <th>Date</th>
                        <th>Symbol</th>
                        <th>Duration</th>
                        <th>Qty</th>
                        <th>Pnl</th>
                        <th>Chart</th>
                        <th>AI</th>
                    </tr>
                    @foreach ($ownedTrades as $ownedTrade)
                        <tr class="border-b border-gray-300 odd:bg-gray-100 even:bg-white text-center h-8">
                            <th>
                                <label>
                                    <input type="checkbox" className="checkbox" />
                                </label>
                            </th>
                            <td>
                                {{ \Carbon\Carbon::parse($ownedTrade->close_datetime)->format('M d, Y') }}
                            </td>
                            <td>
                                {{ $ownedTrade->symbol }}
                            </td>
                            <td>

                            </td>
                            <td>
                                {{ $ownedTrade->quantity }}
                            </td>
                            <td>
                                {{ $ownedTrade->total_pnl }}
                            </td>
                            <td>
                                <button class="btn btn-ghost btn-sm"
                                    onclick="modal_chart_{{ $ownedTrade->id }}.showModal()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                </button>
                                <dialog id="modal_chart_{{ $ownedTrade->id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="text-lg font-bold">Hello!</h3>
                                        <p class="py-4">Press ESC key or click outside to close</p>
                                    </div>
                                    <form method="dialog" class="modal-backdrop">
                                        <button>close</button>
                                    </form>
                                </dialog>
                            </td>
                            <td class="">
                                <button class="btn btn-ghost btn-sm" onclick="modal_ai_{{ $ownedTrade->id }}.showModal()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-500">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.456-2.454L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.454 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                    </svg>
                                </button>
                                <dialog id="modal_ai_{{ $ownedTrade->id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="text-lg font-bold">Hello!</h3>
                                        <p class="py-4">Press ESC key or click outside to close</p>
                                    </div>
                                    <form method="dialog" class="modal-backdrop">
                                        <button>close</button>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
            {{ $ownedTrades->links() }}
        </div>
    </div>

</x-layouts.app>