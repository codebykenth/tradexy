<x-layouts.app title="Market Insights Review - Tradexy">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-10">
            <div>
                <a href="{{ route('daily-news.index') }}" class="btn btn-xs btn-ghost gap-2 font-black uppercase tracking-widest opacity-60 hover:opacity-100 mb-4 pl-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Back to Insights
                </a>
                <h1 class="text-4xl font-black tracking-tight text-base-content uppercase leading-none italic">
                    Market <span class="text-primary">Intelligence</span> Brief
                </h1>
                <p class="text-base-content/60 mt-2 font-medium uppercase tracking-widest text-xs">
                    Report Date: {{ $news->created_at->format('M d, Y') }} • Timeframe: {{ $news->date_range }}
                </p>
            </div>
            <div class="badge badge-primary badge-outline font-black uppercase tracking-widest text-[10px] py-3">
                AI Generated Research
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Gold Section --}}
            @if(isset($news->ai_analysis['gold']))
                @php
                    $gold = $news->ai_analysis['gold'];
                    $gd = $gold['data'] ?? [];
                    $goldBias = $gold['bias'] ?? 'N/A';
                @endphp
                <div class="card bg-base-100 shadow-2xl shadow-base-300/30 border border-base-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#d4af37]/10 to-transparent px-8 py-6 border-b border-base-200 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#d4af37] flex items-center justify-center text-white shadow-lg shadow-[#d4af37]/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M21 16.5c0 .38-.21.71-.53.88l-7.97 4.15c-.32.17-.71.17-1.03 0l-7.97-4.15c-.32-.17-.53-.5-.53-.88v-9c0-.38.21-.71.53-.88l7.97-4.15c.32-.17.71-.17 1.03 0l7.97 4.15c.32.17.53.5.53.88v9z"/></svg>
                            </div>
                            <h2 class="text-2xl font-black uppercase tracking-tighter italic">Gold <span class="text-[#d4af37]">Update</span></h2>
                        </div>
                        <div @class([
                            'px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest shadow-sm',
                            'bg-success text-success-content' => $goldBias === 'Bullish',
                            'bg-error text-error-content' => $goldBias === 'Bearish',
                            'bg-base-200 text-base-content opacity-50' => !in_array($goldBias, ['Bullish', 'Bearish']),
                        ])>
                            {{ $goldBias }}
                        </div>
                    </div>
                    <div class="card-body p-8 space-y-8">
                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-base-200/50 p-4 rounded-2xl text-center">
                                <p class="text-[9px] font-black uppercase opacity-40 mb-1">Confidence</p>
                                <p class="text-lg font-black italic">{{ $gold['confidence'] ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-base-200/50 p-4 rounded-2xl text-center">
                                <p class="text-[9px] font-black uppercase opacity-40 mb-1">24H Flow</p>
                                <p class="text-lg font-black italic">{{ $gd['summary']['price_direction_24h'] ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-base-200/50 p-4 rounded-2xl text-center">
                                <p class="text-[9px] font-black uppercase opacity-40 mb-1">7D Outlook</p>
                                <p class="text-lg font-black italic">{{ $gd['summary']['price_direction_7d'] ?? 'N/A' }}</p>
                            </div>
                        </div>

                        {{-- Main Driver --}}
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-widest opacity-40 mb-3 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#d4af37]"></span> Core Market Driver
                            </h3>
                            <div class="bg-base-200/30 border border-base-200 p-6 rounded-3xl">
                                <p class="text-lg font-black tracking-tight mb-2">{{ $gd['key_driver']['theme'] ?? 'N/A' }}</p>
                                <p class="text-sm opacity-70 leading-relaxed font-medium">{{ $gd['key_driver']['explanation'] ?? 'No explanation available.' }}</p>
                            </div>
                        </div>

                        {{-- Context --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest opacity-40 mb-3">USD Dynamics</h3>
                                <p class="text-sm font-bold opacity-80">{{ $gd['market_context']['usd_dynamics'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest opacity-40 mb-3">Risk Sentiment</h3>
                                <p class="text-sm font-bold opacity-80">{{ $gd['market_context']['risk_sentiment'] ?? 'N/A' }}</p>
                            </div>
                        </div>

                        {{-- Risk Factors --}}
                        <div>
                             <h4 class="text-xs font-black uppercase tracking-widest opacity-40 mb-4">Risk Profile</h4>
                             <div class="space-y-3">
                                @forelse($gd['risk_factors'] ?? [] as $risk)
                                    <div class="flex gap-3 text-sm font-medium">
                                        <div class="mt-1.5 w-1.5 h-1.5 rounded-full bg-error shrink-0"></div>
                                        <p class="opacity-80">
                                            @if(is_array($risk))
                                                <span class="font-black text-base-content">{{ $risk['theme'] ?? '' }}:</span> {{ $risk['explanation'] ?? '' }}
                                            @else
                                                {{ $risk }}
                                            @endif
                                        </p>
                                    </div>
                                @empty
                                    <p class="text-sm italic opacity-40">No significant risks identified.</p>
                                @endforelse
                             </div>
                        {{-- Top Signal --}}
                        @if(isset($gd['top_news_source']) && !empty($gd['top_news_source']['headline']))
                            <div class="pt-6 border-t border-base-200">
                                <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-primary mb-3">Top Market Signal</h4>
                                <div class="bg-primary/5 rounded-2xl p-4 border border-primary/10">
                                    <p class="text-sm font-black leading-tight mb-2">{{ $gd['top_news_source']['headline'] }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-[10px] uppercase font-bold opacity-40">{{ $gd['top_news_source']['source_name'] }}</span>
                                        <a href="{{ $gd['top_news_source']['url'] }}" target="_blank" class="btn btn-xs btn-primary font-black uppercase tracking-widest text-[9px]">Verify Source</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
@endif

            {{-- Bitcoin Section --}}
            @if(isset($news->ai_analysis['crypto']))
                @php
                    $crypto = $news->ai_analysis['crypto'];
                    $cd = $crypto['data'] ?? [];
                    $btcBias = $crypto['bias'] ?? 'N/A';
                @endphp
                <div class="card bg-base-100 shadow-2xl shadow-base-300/30 border border-base-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#f7931a]/10 to-transparent px-8 py-6 border-b border-base-200 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#f7931a] flex items-center justify-center text-white shadow-lg shadow-[#f7931a]/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.643 14.91c-.508 2.042-2.027 3.606-4.077 4.077l-.63 2.503-1.554.391.623-2.47c-.417.104-.844.195-1.278.272l-.624 2.475-1.554.391.631-2.503c-3 source-.104.195-.844.272l-.624 2.475-1.554.391.631-2.503-3.376-.851.39-1.555 1.05.265c.571.144.819-.104.99-.403l1.868-7.447c.046-.242-.03-.453-.298-.521l-1.045-.264.39-1.555 3.376.85.626-2.483 1.554-.391-.624 2.476c.437-.107.868-.201 1.294-.289l.623-2.475 1.554-.391-.632 2.511c4.085.508 5.604 2.027 5.134 5.111-.341 2.228-1.745 3.391-3.6 3.692 1.624.373 2.723 1.244 3.013 3.344zm-7.61-4.634c-.13 3.996-5.161 3.236-4.996 0 .151-3.031 4.863-3.031 4.996 0zm2.251 4.634c-.13 3.996-5.161 3.236-4.996 0 .151-3.031 4.863-3.031 4.996 0z"/></svg>
                            </div>
                            <h2 class="text-2xl font-black uppercase tracking-tighter italic text-[#f7931a]">Bitcoin</h2>
                        </div>
                        <div @class([
                            'px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest shadow-sm',
                            'bg-success text-success-content' => $btcBias === 'Bullish',
                            'bg-error text-error-content' => $btcBias === 'Bearish',
                            'bg-base-200 text-base-content opacity-50' => !in_array($btcBias, ['Bullish', 'Bearish']),
                        ])>
                            {{ $btcBias }}
                        </div>
                    </div>
                    <div class="card-body p-8 space-y-8">
                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-base-200/50 p-4 rounded-2xl text-center">
                                <p class="text-[9px] font-black uppercase opacity-40 mb-1">Confidence</p>
                                <p class="text-lg font-black italic">{{ $crypto['confidence'] ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-base-200/50 p-4 rounded-2xl text-center">
                                <p class="text-[9px] font-black uppercase opacity-40 mb-1">Trend Direction</p>
                                <p class="text-lg font-black italic">{{ $cd['summary']['trend_direction'] ?? 'N/A' }}</p>
                            </div>
                        </div>

                        {{-- Main Driver --}}
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-widest opacity-40 mb-3 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#f7931a]"></span> Core Market Driver
                            </h3>
                            <div class="bg-base-200/30 border border-base-200 p-6 rounded-3xl">
                                <p class="text-lg font-black tracking-tight mb-2">{{ $cd['key_driver']['theme'] ?? 'N/A' }}</p>
                                <p class="text-sm opacity-70 leading-relaxed font-medium">{{ $cd['key_driver']['explanation'] ?? 'No explanation available.' }}</p>
                            </div>
                        </div>

                         {{-- Context --}}
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest opacity-40 mb-3">Liquidity Depth</h3>
                                <p class="text-sm font-bold opacity-80">{{ $cd['market_context']['liquidity'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest opacity-40 mb-3">Institutional Flows</h3>
                                <p class="text-sm font-bold opacity-80">{{ $cd['market_context']['institutional_flows'] ?? 'N/A' }}</p>
                            </div>
                        </div>

                         {{-- Risk Factors --}}
                         <div>
                            <h4 class="text-xs font-black uppercase tracking-widest opacity-40 mb-4">Risk Profile</h4>
                            <div class="space-y-3">
                               @forelse($cd['risk_factors'] ?? [] as $risk)
                                   <div class="flex gap-3 text-sm font-medium">
                                       <div class="mt-1.5 w-1.5 h-1.5 rounded-full bg-error shrink-0"></div>
                                       <p class="opacity-80">
                                           @if(is_array($risk))
                                               <span class="font-black text-base-content">{{ $risk['theme'] ?? '' }}:</span> {{ $risk['explanation'] ?? '' }}
                                           @else
                                               {{ $risk }}
                                           @endif
                                       </p>
                                   </div>
                               @empty
                                   <p class="text-sm italic opacity-40">No significant risks identified.</p>
                               @endforelse
                        {{-- Top Signal --}}
                        @if(isset($cd['top_news_source']) && !empty($cd['top_news_source']['headline']))
                            <div class="pt-6 border-t border-base-200">
                                <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-[#f7931a] mb-3">Top Market Signal</h4>
                                <div class="bg-[#f7931a]/5 rounded-2xl p-4 border border-[#f7931a]/10">
                                    <p class="text-sm font-black leading-tight mb-2">{{ $cd['top_news_source']['headline'] }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-[10px] uppercase font-bold opacity-40">{{ $cd['top_news_source']['source_name'] }}</span>
                                        <a href="{{ $cd['top_news_source']['url'] }}" target="_blank" class="btn btn-xs btn-[#f7931a] bg-[#f7931a] text-white hover:bg-[#e88a1a] border-none font-black uppercase tracking-widest text-[9px]">Verify Source</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
@endif
        </div>

        {{-- Footer Warning --}}
        <div class="mt-12 py-8 border-t border-base-300 text-center">
             <div class="max-w-2xl mx-auto space-y-4">
                <div class="flex justify-center gap-4">
                    <div class="badge badge-outline badge-ghost text-[9px] font-black uppercase tracking-widest">Model: Gemini 2.0 Flash</div>
                    <div class="badge badge-outline badge-ghost text-[9px] font-black uppercase tracking-widest">Aggregator: Tradexy Feed v2</div>
                </div>
                <p class="text-[10px] uppercase font-black tracking-widest opacity-40 leading-relaxed">
                    This report is AI-generated for informational purposes only. Not financial advice. Always Do Your Own Research (DYOR). Market conditions can change rapidly.
                </p>
             </div>
        </div>
    </div>
</x-layouts.app>
