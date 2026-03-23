<x-layouts.app title="Win/Loss Gallery - Tradexy">
    <div class="max-w-7xl mx-auto px-6 space-y-4 mb-8 w-full">
        <x-page-title title="Win/Loss Gallery" subtitle="Visual overview of your trade charts separated into wins and losses" />
        
        <div class="flex flex-col md:flex-row gap-8 mt-6">
            <!-- Left Column: Wins -->
            <div class="flex-1 w-full md:w-1/2">
                <div class="flex items-center gap-2 mb-6 border-b border-base-300 pb-2">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <h2 class="text-xl font-bold text-base-content uppercase tracking-wide">Winning Trades</h2>
                </div>
                
                @if($winningTrades->isNotEmpty())
                    <div class="grid grid-cols-1 gap-6" id="wins-container">
                        @foreach($winningTrades as $trade)
                            <div class="card bg-base-100 shadow-md overflow-hidden cursor-pointer hover:shadow-lg hover:border-green-300 transition-all duration-300 border border-green-100" onclick="window.location='{{ route('trades.show', $trade->id) }}'">
                                <figure class="h-64 bg-base-300">
                                    <x-optimized-image :src="$trade->direct_chart_url ?? $trade->chart_picture" alt="Chart" class="w-full h-full" object="contain" />
                                </figure>
                                <div class="card-body p-4 bg-success/10">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-black uppercase text-base-content">{{ $trade->symbol }}</h3>
                                            <p class="text-xs font-medium text-base-content/60 mt-0.5">{{ \Carbon\Carbon::parse($trade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y') }}</p>
                                        </div>
                                        <div class="badge badge-success font-bold border-none shadow-sm text-success-content">
                                            +${{ number_format($trade->total_pnl, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($winningTrades->hasMorePages())
                        <div class="mt-6 flex justify-center load-more-wrapper">
                            <button class="btn btn-outline btn-sm w-full load-more-btn" data-url="{{ $winningTrades->nextPageUrl() }}" data-target="wins-container">
                                Load More Wins
                            </button>
                        </div>
                    @endif
                @else
                    <div class="bg-base-200 border-2 border-dashed border-base-300 rounded-xl p-8 text-center mt-4 text-sm text-base-content/60">
                        <span class="block text-2xl mb-2">🌱</span>
                        No winning charts available yet.
                    </div>
                @endif
            </div>

            <!-- Right Column: Losses -->
            <div class="flex-1 w-full md:w-1/2">
                <div class="flex items-center gap-2 mb-6 border-b border-base-300 pb-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <h2 class="text-xl font-bold text-base-content uppercase tracking-wide">Losing Trades</h2>
                </div>
                
                @if($losingTrades->isNotEmpty())
                    <div class="grid grid-cols-1 gap-6" id="losses-container">
                        @foreach($losingTrades as $trade)
                            <div class="card bg-base-100 shadow-md overflow-hidden cursor-pointer hover:shadow-lg hover:border-red-300 transition-all duration-300 border border-red-100" onclick="window.location='{{ route('trades.show', $trade->id) }}'">
                                <figure class="h-64 bg-base-300">
                                    <x-optimized-image :src="$trade->direct_chart_url ?? $trade->chart_picture" alt="Chart" class="w-full h-full" object="contain" />
                                </figure>
                                <div class="card-body p-4 bg-error/10">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-black uppercase text-base-content">{{ $trade->symbol }}</h3>
                                            <p class="text-xs font-medium text-base-content/60 mt-0.5">{{ \Carbon\Carbon::parse($trade->close_datetime, 'UTC')->setTimezone('Asia/Manila')->format('M d, Y') }}</p>
                                        </div>
                                        <div class="badge badge-error font-bold border-none shadow-sm text-error-content">
                                            -${{ number_format(abs($trade->total_pnl), 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($losingTrades->hasMorePages())
                        <div class="mt-6 flex justify-center load-more-wrapper">
                            <button class="btn btn-outline btn-sm w-full load-more-btn" data-url="{{ $losingTrades->nextPageUrl() }}" data-target="losses-container">
                                Load More Losses
                            </button>
                        </div>
                    @endif
                @else
                    <div class="bg-base-200 border-2 border-dashed border-base-300 rounded-xl p-8 text-center mt-4 text-sm text-base-content/60">
                        <span class="block text-2xl mb-2">🛡️</span>
                        No losing charts available yet.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.load-more-btn').forEach(button => {
                button.addEventListener('click', async function() {
                    const url = this.getAttribute('data-url');
                    const targetId = this.getAttribute('data-target');
                    const container = document.getElementById(targetId);
                    const wrapper = this.closest('.load-more-wrapper');
                    
                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Loading...';
                    this.disabled = true;

                    try {
                        // Fetch the next page HTML
                        const response = await fetch(url);
                        const htmlText = await response.text();
                        
                        // Parse it into a DOM
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(htmlText, 'text/html');
                        
                        // Extract new items
                        const newContainer = doc.getElementById(targetId);
                        if(newContainer) {
                            container.insertAdjacentHTML('beforeend', newContainer.innerHTML);
                        }
                        
                        // Extract new load more button wrapper
                        // Find the same load more wrapper in the new Document that corresponds to this column (either wins-container or losses-container)
                        const newBtnRaw = doc.querySelector(`button[data-target="${targetId}"]`);
                        
                        if(newBtnRaw) {
                            // Replace this button's wrapper with the new one
                            const newWrapper = newBtnRaw.closest('.load-more-wrapper');
                            wrapper.outerHTML = newWrapper.outerHTML;
                            
                            // Re-bind the event to the newly rendered button!
                            const newlyAddedBtn = document.querySelector(`button[data-target="${targetId}"]`);
                            if(newlyAddedBtn) {
                                newlyAddedBtn.addEventListener('click', arguments.callee);
                            }
                        } else {
                            // No more pages, remove the button
                            wrapper.remove();
                        }
                        
                    } catch (error) {
                        console.error('Error loading more items:', error);
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                });
            });
        });
    </script>
</x-layouts.app>
