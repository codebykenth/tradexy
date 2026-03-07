<x-layouts.app>
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('trades.index') }}"><- Back to Trades</a>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('trades.edit', $trade->id ?? 1) }}"
                    class="flex items-center gap-2 p-3 text-primary hover:bg-primary/10 rounded-lg transition-colors cursor-pointer font-semibold btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    <p>Edit Log</p>
                </a>

                <button type="button" class="btn btn-error"
                    onclick="event.stopPropagation(); document.getElementById('delete_confirmation_modal_{{ $trade->id }}').showModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <p>Delete</p>
                </button>
            </div>
        </div>
        <!-- Delete Confirmation Modal -->
        <dialog id="delete_confirmation_modal_{{ $trade->id }}" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">Confirm Deletion</h3>
                <p class="py-4">Are you sure you want to delete this trade entry? This action cannot be undone.</p>
                <div class="modal-action">
                    <!-- method="dialog" closes the modal without a page refresh -->
                    <form method="dialog">
                        <button class="btn">Cancel</button>
                    </form>
                    <form action="{{ route('trades.destroy', $trade->id) }}" method="post">
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
        <div class="flex gap-4">
            <!-- Left -->
            <div class="bg-gray-100 rounded-lg p-8 mt-8 w-2/3 space-y-4">
                <!-- General Information -->
                <div class="flex items-center justify-between gap-4 w-full">
                    <div>
                        <div class="flex items-center gap-4">
                            <p class="text-4xl uppercase font-black">{{ $trade->symbol ?? 'N/A'  }}</p>
                            <div @class([
                                "badge",
                                "uppercase",
                                "badge-success" => $trade->exit_side === "short",
                                "badge-error" => $trade->exit_side === "long"
                            ])>{{ $trade->entry_side }}</div>
                            <div class="badge badge-neutral">{{ $trade->leverage ?? 'N/A'  }}x</div>

                        </div>
                        @if ($trade->order_id)
                            <div class="text-sm text-gray-400">
                                <p>Order ID: {{ $trade->order_id ?? 'N/A' }}</p>
                            </div>
                        @endif

                        <div class="flex gap-8 mt-4">
                            <div class="flex items-center gap-2 text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                <div>
                                    <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Open</p>
                                    <p class="font-medium">
                                        {{ $trade->open_datetime ? \Carbon\Carbon::parse($trade->open_datetime)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-12 3h12" />
                                </svg>
                                <div>
                                    <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Close</p>
                                    <p class="font-medium">
                                        {{ $trade->close_datetime ? \Carbon\Carbon::parse($trade->close_datetime)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <div>
                                    <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Duration</p>
                                    <p class="font-medium">{{ $trade->duration }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="uppercase">Net P&L</p>
                        <p @class([
                            'text-5xl',
                            'text-red-500' => $trade->total_pnl < 0,
                            'text-green-500' => $trade->total_pnl > 0,
                        ])>${{ number_format($trade->total_pnl, 2) ?? 'N/A'  }}</p>
                        <p @class([
                            'text-sm',
                            'text-red-400' => $trade->closed_pnl < 0,
                            'text-green-400' => $trade->closed_pnl > 0,
                        ])>Gross: ${{ number_format($trade->closed_pnl, 2) ?? 'N/A'  }}</p>
                        <p @class([
                            'text-sm',
                            'text-red-400'
                        ])>Fees:    ${{ number_format($trade->open_fees + $trade->close_fees, 2) ?? 'N/A'  }}</p>

                    </div>
                </div>
                <div class="border-b border-gray-309">

                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Entry Price</p>
                        <p>{{ $trade->avg_entry_price ?? 'N/A'  }}</p>
                    </div>
                    <div>
                        <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Exit Price</p>
                        <p>{{ $trade->avg_exit_price ?? 'N/A'  }}</p>
                    </div>
                    <div>
                        <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Quantity</p>
                        <p>{{ $trade->quantity !== null ? (strpos((string) $trade->quantity, '.') !== false ? rtrim(rtrim((string) $trade->quantity, '0'), '.') : $trade->quantity) : 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Stoploss</p>
                        <p>{{ $trade->stop_loss_price ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Take Profit</p>
                        <p>{{ $trade->take_profit_price ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Risk Reward</p>
                        <p>{{ $trade->risk_reward }}</p>
                    </div>
                </div>
            </div>
            <!-- Right -->
            <div class="bg-gray-100 rounded-lg p-8 mt-8 w-1/3 space-y-4">
                <p class="uppercase font-bold mb-4">Setup Context</p>
                <div>
                    <p class="uppercase text-xs font-bold text-gray-500 tracking-wider mb-2">Strategy</p>
                    @if($trade->strategy)
                        <p>{{ $trade->strategy->name }}</p>
                    @else
                        <p class="italic text-gray-500">No strategy assigned</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="uppercase text-xs font-bold text-gray-500 tracking-wider mb-2">Timeframe</p>
                        <p>{{ $trade->timeframe ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="uppercase text-xs font-bold text-gray-500 tracking-wider mb-2">Session</p>
                        <p>{{ $trade->session }}</p>
                    </div>
                </div>
                <div class="border-b border-gray-300"></div>
                <div class="">
                    <p class="uppercase text-xs font-bold text-gray-500 tracking-wider mb-2">Emotional State</p>
                    <div class="flex items-center gap-4 w-full">
                        <div class="bg-white rounded-lg p-3 flex-1 text-center border border-gray-200 shadow-sm">
                            <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Entry</p>
                            <p class="font-medium text-gray-800">{{ $trade->entry_emotion ?? '-' }}</p>
                        </div>
                        <div class="text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                        <div class="bg-white rounded-lg p-3 flex-1 text-center border border-gray-200 shadow-sm">
                            <p class="uppercase text-xs font-bold text-gray-500 tracking-wider">Exit</p>
                            <p class="font-medium text-gray-800">{{ $trade->exit_emotion ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="flex gap-4">
            <!-- Left -->
            <div class="bg-gray-100 rounded-lg p-8 mt-8 w-2/3">
                <!-- Chart Snapshot -->
                <div class="flex items-center gap-3 text-gray-800  mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-8 text-primary">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    <p class="text-2xl font-bold">Chart Snapshot</p>
                </div>
                @if($trade->chart_picture)
                    <img src="{{ $trade->direct_chart_url ?? "" }}" alt=""
                        class="cursor-pointer mt-4 rounded-lg shadow-sm hover:scale-[1.02] transition-transform duration-300 ease-in-out"
                        onclick="chartModal.showModal()">
                    <dialog id="chartModal" class="modal">
                        <div class="modal-box w-11/12 max-w-[75vw]">
                            <img src="{{ $trade->direct_chart_url ?? "" }}" alt="Chart Snapshot"
                                class="w-full rounded-lg shadow-sm">
                            <div class="flex justify-end w-full mt-4">
                                <a href="{{ $trade->chart_picture }}" target="_blank" class="btn btn-primary">
                                    View Image
                                </a>
                            </div>
                        </div>
                        <form method="dialog" class="modal-backdrop">
                            <button>close</button>
                        </form>
                    </dialog>
                @else
                    <p class="italic text-gray-500">No chart image yet.</p>
                @endif

            </div>
            <!-- Right -->
            <div class="bg-gray-100 rounded-lg p-8 mt-8 w-1/3 space-y-4">
                <p class="uppercase font-bold mb-4">Trade Logic</p>
                <div>
                    <p class="uppercase text-xs font-bold text-gray-500 tracking-wider mb-2">Entry Triggers</p>
                    @if($trade->reasons->isNotEmpty())
                        <ul class="list-disc ml-4 space-y-2 text-gray-700">
                            @foreach($trade->reasons as $reason)
                                @if($reason->type == 'entry')
                                    <li>{{ $reason->reason }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="italic text-gray-500">No entry reasons logged.</p>
                    @endif
                </div>
                <div>
                    <p class="uppercase text-xs font-bold text-gray-500 tracking-wider mb-2">Exit Triggers</p>
                    @if($trade->reasons->isNotEmpty())
                        <ul class="list-disc ml-4 space-y-2 text-gray-700">
                            @foreach($trade->reasons as $reason)
                                @if($reason->type == 'exit')
                                    <li>{{ $reason->reason }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="italic text-gray-500">No exit reasons logged.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="bg-gray-100 rounded-lg p-8 my-8 w-2/3">
                <div class="flex items-center gap-3 text-indigo-900 mb-6">
                    <div class="bg-indigo-100 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6 text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.456-2.454L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.454 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                        </svg>
                    </div>
                    <p class="text-2xl font-bold">AI Analysis</p>
                </div>
                @if($trade->ai_analysis)
                                <div
                                    class="text-left text-gray-700 text-[15px] leading-relaxed [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-gray-900 [&_h3]:mt-8 [&_h3]:mb-4 [&_h3]:border-b [&_h3]:border-gray-200 [&_h3]:pb-2 [&_h3]:flex [&_h3]:items-center [&_h3]:gap-2 [&_p]:mb-4 [&_p]:text-gray-600 [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-6 [&_ul]:space-y-2 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-6 [&_ol]:space-y-2 [&_li]:text-gray-700 [&_strong]:font-bold [&_strong]:text-gray-900 max-h-[65vh] overflow-y-auto pr-4 scrollbar-thin">

                                    {!! \Illuminate\Support\Str::markdown($trade->ai_analysis, [
                        'html_input' => 'strip',
                        'allow_unsafe_links' => false
                    ]) !!}

                                </div>
                @else
                    <p class="italic text-gray-500">No AI analysis yet.</p>
                    @if($trade->chart_picture)
                        <form action="/analyze/{{ $trade->id }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-primary btn-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.456-2.454L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.454 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                </svg>
                                Generate AI Analysis
                            </button>
                        </form>
                    @endif
                @endif
            </div>
            <div class="bg-gray-100 rounded-lg p-8 my-8 w-1/3">
                <p class="uppercase font-bold mb-4">Key Takeaways</p>
                @if($trade->lessons->isNotEmpty())
                    <ul class="list-disc ml-4 space-y-2 text-gray-700">
                        @foreach($trade->lessons as $lesson)
                            <li>{{ $lesson->lesson }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="italic text-gray-500">No lessons recorded for this trade.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>