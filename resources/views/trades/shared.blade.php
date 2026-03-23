@php
    $winLoss = $trade->total_pnl > 0 ? 'Win' : 'Loss';
    $pnlAmount = number_format(abs($trade->total_pnl), 2);
    $shareDescription = $trade->symbol . ' ' . $trade->entry_side . ' | ' . $winLoss . ' of $' . $pnlAmount . ' | Leverage: ' . $trade->leverage . 'x';
@endphp
<x-layouts.app 
    :title="$trade->symbol . ' Trade Review | ' . config('app.name')"
    :description="$shareDescription"
    :image="$trade->direct_chart_url ?? $trade->chart_picture ?? asset('images/logo.png')"
>
    <div class="max-w-5xl mx-auto px-6 py-8">
        <div class="mb-6 flex items-center justify-between mt-4">
            <span class="badge w-full md:w-auto badge-outline text-xs border-primary text-primary uppercase tracking-wider font-bold">Shared Trade Review</span>
        </div>
        <!-- Trade Header -->
        <div class="bg-base-100 rounded-xl p-8 shadow-sm border border-base-300">
            <div class="flex items-center justify-between gap-4 w-full">
                <div>
                    <div class="flex items-center gap-4">
                        <p class="text-4xl uppercase font-black">{{ $trade->symbol ?? 'N/A' }}</p>
                        <div class="badge badge-outline uppercase text-xs font-bold">{{ $trade->market ?? 'crypto' }}</div>
                        <div @class([
                            "badge uppercase",
                            "badge-success" => $trade->exit_side === "short",
                            "badge-error" => $trade->exit_side === "long"
                        ])>{{ $trade->entry_side }}</div>
                        <div class="badge badge-neutral">{{ $trade->leverage ?? 'N/A' }}x</div>
                        @if($trade->is_demo)
                            <div class="badge badge-warning uppercase text-xs font-bold font-mono">Demo</div>
                        @endif
                    </div>

                    <div class="flex gap-8 mt-4">
                        <div class="flex items-center gap-2 text-base-content/70">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-base-content/40">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            <div>
                                <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider">Open</p>
                                <p class="font-medium text-sm">{{ $trade->open_datetime ? \Carbon\Carbon::parse($trade->open_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y h:i A') : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-base-content/70">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-base-content/40">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-12 3h12" />
                            </svg>
                            <div>
                                <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider">Close</p>
                                <p class="font-medium text-sm">{{ $trade->close_datetime ? \Carbon\Carbon::parse($trade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y h:i A') : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-base-content/70">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-base-content/40">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <div>
                                <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider">Duration</p>
                                <p class="font-medium text-sm">{{ $trade->duration }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <p class="uppercase text-sm text-base-content/50">Net P&L</p>
                    <p @class([
                        'text-4xl font-black',
                        'text-red-500' => $trade->total_pnl < 0,
                        'text-green-500' => $trade->total_pnl > 0,
                    ])>${{ number_format($trade->total_pnl, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Trade Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="bg-base-100 rounded-xl p-6 shadow-sm border border-base-300">
                <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider">Entry Price</p>
                <p class="text-lg font-semibold mt-1">{{ $trade->avg_entry_price ?? 'N/A' }}</p>
            </div>
            <div class="bg-base-100 rounded-xl p-6 shadow-sm border border-base-300">
                <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider">Exit Price</p>
                <p class="text-lg font-semibold mt-1">{{ $trade->avg_exit_price ?? 'N/A' }}</p>
            </div>
            <div class="bg-base-100 rounded-xl p-6 shadow-sm border border-base-300">
                <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider">Risk Reward</p>
                <p class="text-lg font-semibold mt-1">{{ $trade->risk_reward }}</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-6 mt-6">
            <!-- Chart -->
            <div class="bg-base-100 rounded-xl p-6 shadow-sm border border-base-300 w-full md:w-2/3">
                <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider mb-4">Chart Snapshot</p>
                @if($trade->chart_picture)
                    <img src="{{ $trade->direct_chart_url ?? '' }}" alt="Chart Snapshot" class="w-full rounded-lg shadow-sm cursor-pointer hover:scale-[1.02] transition-transform duration-300 ease-in-out" onclick="chartModal.showModal()">
                    
                    <dialog id="chartModal" class="modal">
                        <div class="modal-box w-11/12 max-w-[75vw]">
                            <img src="{{ $trade->direct_chart_url ?? '' }}" alt="Chart Snapshot" class="w-full rounded-lg shadow-sm">
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
                    <p class="italic text-gray-400">No chart image available.</p>
                @endif
            </div>

            <!-- Setup Context -->
            <div class="bg-base-100 rounded-xl p-6 shadow-sm border border-base-300 w-full md:w-1/3 space-y-4">
                <div>
                    <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider mb-2">Strategy</p>
                    @if($trade->strategy)
                        <p class="font-medium">{{ $trade->strategy->name }}</p>
                    @else
                        <p class="italic text-gray-400">No strategy assigned</p>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider mb-1">Timeframe</p>
                        <p class="font-medium">{{ $trade->timeframe ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider mb-1">Session</p>
                        <p class="font-medium">{{ $trade->session }}</p>
                    </div>
                </div>
                <div class="border-t border-base-300 pt-4">
                    <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider mb-2">Entry Triggers</p>
                    @if($trade->reasons->where('type', 'entry')->isNotEmpty())
                        <ul class="list-disc ml-4 space-y-1 text-base-content/70 text-sm">
                            @foreach($trade->reasons->where('type', 'entry') as $reason)
                                <li>{{ $reason->reason }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="italic text-gray-400 text-sm">No entry reasons logged.</p>
                    @endif
                </div>
                <div class="border-t border-base-300 pt-4">
                    <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider mb-2">Exit Triggers</p>
                    @if($trade->reasons->where('type', 'exit')->isNotEmpty())
                        <ul class="list-disc ml-4 space-y-1 text-base-content/70 text-sm">
                            @foreach($trade->reasons->where('type', 'exit') as $reason)
                                <li>{{ $reason->reason }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="italic text-gray-400 text-sm">No exit reasons logged.</p>
                    @endif
                </div>
                <div class="border-t border-base-300 pt-4">
                    <p class="uppercase text-xs font-bold text-base-content/50 tracking-wider mb-2">Lessons Learned</p>
                    @if($trade->lessons->isNotEmpty())
                        <ul class="list-disc ml-4 space-y-1 text-base-content/70 text-sm">
                            @foreach($trade->lessons as $lesson)
                                <li>{{ $lesson->lesson }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="italic text-gray-400 text-sm">No lessons recorded.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- AI Analysis -->
        @if($trade->ai_analysis)
            <div class="bg-base-100 rounded-xl p-6 shadow-sm border border-base-300 mt-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-indigo-100 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.456-2.454L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.454 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                        </svg>
                    </div>
                    <p class="text-xl font-bold">AI Analysis</p>
                </div>
                <div class="text-left text-base-content/80 text-[15px] leading-relaxed [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-base-content [&_h3]:mt-8 [&_h3]:mb-4 [&_h3]:border-b [&_h3]:border-base-300 [&_h3]:pb-2 [&_p]:mb-4 [&_p]:text-base-content/60 [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-6 [&_ul]:space-y-2 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-6 [&_ol]:space-y-2 [&_li]:text-base-content/80 [&_strong]:font-bold [&_strong]:text-base-content">
                    {!! \Illuminate\Support\Str::markdown($trade->ai_analysis, [
                        'html_input' => 'strip',
                        'allow_unsafe_links' => false
                    ]) !!}
                </div>
            </div>
        @endif

        <!-- CTA for Visitors -->
        <div class="mt-12 bg-gray-900 dark:bg-zinc-900 rounded-2xl p-8 text-center shadow-xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-indigo-600/20 opacity-50 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative z-10">
                <h3 class="text-2xl font-bold text-white mb-2">Ready to level up your trading?</h3>
                <p class="text-gray-400 mb-6 max-w-lg mx-auto">Join thousands of traders using {{ config('app.name') }} to track setups, analyze data, and build their edge.</p>
                <a href="{{ route('register') }}" class="btn btn-primary btn-wide">
                    Start Your Journal Free
                </a>
            </div>
        </div>
        <div class="text-center mt-6 text-xs text-gray-500 uppercase tracking-widest font-medium">
            Shared via <a href="{{ url('/') }}" class="hover:text-primary transition-colors">{{ config('app.name') }}</a>
        </div>
    </div>
</x-layouts.app>
