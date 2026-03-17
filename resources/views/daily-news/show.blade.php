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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M21.5,13.5L19,6H5L2.5,13.5L3.5,18H20.5L21.5,13.5M16.5,14H7.5V12H16.5V14Z"/>
                                </svg>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 512 512">
                                    <path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zm-141.651-35.33c4.935-32.928-20.154-50.596-54.397-62.396l11.119-44.544-27.108-6.758-10.825 43.412c-7.12-1.778-14.453-3.443-21.722-5.105l10.893-43.682-27.114-6.764-11.119 44.57c-5.896-1.342-11.666-2.673-17.301-4.066l.014-.064-37.4 9.333 7.211 28.914s20.122-4.612 19.704-4.25c10.987 2.743 14.633 10.016 14.262 15.79l-14.28 57.262c.858.214 1.973.524 3.193.847l-3.213-.803-20.015 80.24c-1.347 3.328-4.743 8.322-12.433 6.406.273.392-19.71-4.918-19.71-4.918l-13.456 31.027 35.29 8.805c6.563 1.64 13.012 3.341 19.346 4.965l-11.238 45.066 27.108 6.761 11.119-44.574c7.391 2.01 14.567 3.899 21.572 5.698l-11.096 44.507 27.114 6.764 11.241-45.093c46.221 8.749 80.958 5.222 95.59-36.567 11.79-33.662-1.126-53.059-25.46-65.719 17.722-4.09 31.065-15.751 34.621-39.757zm-62.152 86.842c-8.386 33.651-65.138 15.46-83.501 10.891l14.898-59.72c18.359 4.567 77.108 13.628 68.603 48.829zm10.354-87.31c-7.644 30.663-54.908 15.111-70.211 11.291l13.528-54.218c15.303 3.821 64.429 10.954 56.683 42.927z"/>
                                </svg>
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
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-base-200/50 p-4 rounded-2xl text-center">
                                <p class="text-[9px] font-black uppercase opacity-40 mb-1">Confidence</p>
                                <p class="text-lg font-black italic">{{ $crypto['confidence'] ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-base-200/50 p-4 rounded-2xl text-center">
                                <p class="text-[9px] font-black uppercase opacity-40 mb-1">24H Flow</p>
                                <p class="text-lg font-black italic">{{ $cd['summary']['price_direction_24h'] ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-base-200/50 p-4 rounded-2xl text-center">
                                <p class="text-[9px] font-black uppercase opacity-40 mb-1">7D Outlook</p>
                                <p class="text-lg font-black italic">{{ $cd['summary']['price_direction_7d'] ?? 'N/A' }}</p>
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
                             </div>
                        </div>

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
