<x-layouts.app title="Strategies - Tradexy">
    <div class="max-w-7xl mx-auto px-6 space-y-4 mb-8">
        <x-page-title title="Trading Strategies"
            subtitle="Define and track your trading playbooks with performance analytics." />
        @if($strategies->isNotEmpty())
            <div class="relative h-12 mb-4">
                <a href="{{ route('strategies.create') }}" class="btn btn-primary absolute left-0 top-0 h-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Strategy
                </a>
            </div>
            <x-table>
                <x-slot:header>
                    <th class="text-center font-semibold">Set-up</th>
                    <th class="font-semibold">Net P/L</th>
                    <th class="font-semibold">No. of Trades</th>
                    <th class="font-semibold">Total Win Amt.</th>
                    <th class="font-semibold">Total Loss Amt.</th>
                    <th class="font-semibold">Avg Win</th>
                    <th class="font-semibold">Avg Loss</th>
                    <th class="font-semibold">Hit Ratio (%)</th>
                    <th class="font-semibold">Edge Ratio (x)</th>
                    <th class="font-semibold">Action</th>
                </x-slot:header>
                @foreach ($strategies as $strategy)
                    <x-table.row onclick="window.location='{{ route('strategies.show', $strategy->id) }}'">
                        <td class="font-medium">
                            {{ $strategy->name }}
                        </td>
                        <td @class([
                            'text-green-500 font-bold' => $strategy->net_pnl > 0,
                            'text-red-500 font-bold' => $strategy->net_pnl < 0
                        ])>
                            ${{ number_format($strategy->net_pnl, 2) }}
                        </td>
                        <td>
                            {{ $strategy->trades_count }}
                        </td>
                        <td>
                            ${{ number_format($strategy->total_win_amount, 2) }}
                        </td>
                        <td>
                            -${{ number_format(abs($strategy->total_loss_amount), 2) }}
                        </td>
                        <td class="text-green-500">
                            ${{ number_format($strategy->avg_win, 2) }}
                        </td>
                        <td class="text-red-500">
                            -${{ number_format(abs($strategy->avg_loss), 2) }}
                        </td>
                        <td @class([
                            'text-black' => $strategy->hit_ratio == 0,
                            'text-orange-500 font-bold' => $strategy->hit_ratio == 50,
                            'text-green-500 font-bold' => $strategy->hit_ratio > 50,
                            'text-red-500 font-bold' => $strategy->hit_ratio < 50 && $strategy->hit_ratio != 0,
                        ])>
                            {{ number_format($strategy->hit_ratio, 1) }}%
                        </td>
                        <td @class([
                            'text-black' => $strategy->edge_ratio == 0,
                            'text-red-500 font-bold' => $strategy->edge_ratio > 0 && $strategy->edge_ratio < 1,
                            'text-orange-500 font-bold' => $strategy->edge_ratio == 1,
                            'text-green-500 font-bold' => $strategy->edge_ratio > 1 && $strategy->edge_ratio <= 2,
                            'text-emerald-600 font-bold' => $strategy->edge_ratio > 2,
                        ])>
                            {{ number_format($strategy->edge_ratio, 2) }}x
                        </td>
                        <td>
                            <div class="flex gap-4 items-center justify-center">
                                <button type="button"
                                    onclick="event.stopPropagation(); window.location.href='{{ route('strategies.edit', $strategy->id) }}'"
                                    class="text-blue-500 hover:text-blue-700 transition cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                <button type="button"
                                    onclick="event.stopPropagation(); document.getElementById('delete_confirmation_modal_{{ $strategy->id }}').showModal()"
                                    class="text-red-500 hover:text-red-700 transition cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </x-table.row>
                @endforeach
            </x-table>
        @else
            <div class="mt-8">
                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-12 h-12 mx-auto text-gray-400 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <h3 class="font-bold text-lg text-gray-900 mb-2">No strategies added yet</h3>
                    <p class="text-gray-500 mb-4">Create your first strategy to start tracking your rules and edge.</p>
                    <a href="{{ route('strategies.create') }}" class="btn btn-primary">
                        + Create Strategy
                    </a>
                </div>
            </div>
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
        @endforeach
    </div>
</x-layouts.app>
@include('components.form-dirty-state-check')