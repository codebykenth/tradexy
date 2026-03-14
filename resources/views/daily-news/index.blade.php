<x-layouts.app title="Market Insights - Tradexy">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <x-page-title title="Market Insights" subtitle="AI-powered macro analysis and news sentiment" />

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($allNews as $news)
                @php
                    $analysis = $news->ai_analysis;
                    $goldBias = $analysis['gold']['bias'] ?? 'N/A';
                    $btcBias = $analysis['crypto']['bias'] ?? 'N/A';
                @endphp
                <a href="{{ route('daily-news.show', $news->id) }}" class="group">
                    <div class="card bg-base-100 shadow-xl border border-base-200 hover:border-primary/50 transition-all duration-300">
                        <div class="card-body p-6">
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-xs font-black uppercase tracking-widest opacity-40">{{ $news->created_at->format('M d, Y') }}</span>
                                <div class="flex gap-2">
                                    <span @class([
                                        'badge badge-xs font-black uppercase tracking-tighter py-2',
                                        'badge-success' => $goldBias === 'Bullish',
                                        'badge-error' => $goldBias === 'Bearish',
                                        'badge-ghost' => !in_array($goldBias, ['Bullish', 'Bearish']),
                                    ])>Gold: {{ $goldBias }}</span>
                                    <span @class([
                                        'badge badge-xs font-black uppercase tracking-tighter py-2',
                                        'badge-success' => $btcBias === 'Bullish',
                                        'badge-error' => $btcBias === 'Bearish',
                                        'badge-ghost' => !in_array($btcBias, ['Bullish', 'Bearish']),
                                    ])>BTC: {{ $btcBias }}</span>
                                </div>
                            </div>
                            
                            <h3 class="text-xl font-black italic tracking-tight group-hover:text-primary transition-colors">
                                Daily Market <span class="text-primary italic">Brief</span>
                            </h3>
                            <p class="text-xs font-medium opacity-60 mt-1 uppercase tracking-widest">Range: {{ $news->date_range }}</p>
                            
                            <div class="mt-6 flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest opacity-30">Tradexy AI v2</span>
                                <div class="btn btn-circle btn-ghost btn-sm group-hover:bg-primary group-hover:text-white transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-20 text-center bg-base-200/30 rounded-3xl border-2 border-dashed border-base-300">
                    <p class="text-base-content/50 font-black uppercase tracking-widest italic">No insights generated yet.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $allNews->links() }}
        </div>
    </div>
</x-layouts.app>
