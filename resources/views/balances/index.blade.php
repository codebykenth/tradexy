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
                    </tr>
                    @forelse ($balances as $balance)
                        <tr class="border-b border-gray-300 odd:bg-gray-100 even:bg-white hover:bg-gray-200 transition-colors text-center h-12 [&>th:first-child]:pl-4 [&>td:last-child]:pr-4 cursor-pointer"
                            onclick="window.location='/balances/{{ $balance->id }}'">
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
    </div>
</x-layouts.app>