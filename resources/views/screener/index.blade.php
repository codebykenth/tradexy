<x-layouts.app title="Screener — Tradexy" description="Scan crypto markets using technical indicators.">
    <div class="max-w-7xl mx-auto px-6 space-y-4 mb-8">
        
        {{-- Top Bar: Set Filters --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4  mb-6">
            <x-page-title title="Set Filters" />

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" id="indicator-search" placeholder="Search Filter" class="input input-sm input-bordered w-full pl-9 rounded-full bg-base-200/50 focus:bg-base-100 transition-colors">
                </div>
                
                <div class="join">
                    <select name="timeframe" id="global-timeframe" form="screener-form" class="select select-bordered select-sm join-item bg-base-200/50">
                        @foreach($availableTimeframes as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['timeframe'] ?? '1D') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select class="select select-bordered select-sm join-item bg-base-200/50 font-medium">
                        <option>Crypto (USDT)</option>
                        <option disabled>PSE (Coming Soon)</option>
                    </select>
                </div>

                <div class="flex-1 sm:flex-none flex justify-end">
                    <button type="submit" form="screener-form" id="run-screener-btn" class="btn btn-primary btn-sm rounded-full px-6 shadow-sm hover:shadow-md transition-shadow font-bold">
                        Run Screener
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Form wrapping active filters --}}
        <form id="screener-form" method="GET" action="{{ route('screener.index') }}">
            <input type="hidden" name="timeframe" id="form-timeframe" value="{{ $filters['timeframe'] ?? '1D' }}">
            <input type="hidden" name="sort_by" value="{{ $filters['sort_by'] ?? 'volume24h' }}">
            <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] ?? 'desc' }}">

            {{-- Active Filters Box --}}
            <div class="bg-base-200/30 border border-base-300 rounded-2xl p-4 mb-6 sticky top-4 z-10 backdrop-blur-md shadow-sm">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center min-h-[2rem]">
                    <div class="text-xs font-bold uppercase tracking-wider text-base-content/50 whitespace-nowrap pt-1">Active Filters:</div>
                    <div id="active-filters-container" class="flex flex-wrap gap-2 items-center w-full">
                        @if(empty($filters['indicators']))
                            <span id="no-filters-msg" class="text-sm text-base-content/40 italic">None selected. Click indicators below to add.</span>
                        @else
                            @php
                                $operatorLabels = ['gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<=', 'eq' => '=', 'between' => 'Between'];
                            @endphp
                            @foreach($filters['indicators'] as $idx => $ind)
                                @php
                                    $def = \App\Services\ScreenerService::getIndicatorDefinition($ind['key'] ?? '');
                                    $label = $def ? $def['label'] : ($ind['key'] ?? 'Unknown');
                                    $type = $def ? $def['type'] : 'basic';
                                    
                                    $conditionKey = $ind['condition'] ?? '';
                                    $conditionStr = $indicatorConditions[$type][$conditionKey] ?? $conditionKey;
                                @endphp
                                <div class="badge badge-primary gap-1 py-3 px-3 shadow-sm filter-chip" data-index="{{ $idx }}">
                                    <span class="font-semibold">{{ $label }}</span>
                                    <input type="hidden" name="indicators[{{ $idx }}][key]" value="{{ $ind['key'] }}">
                                    
                                    <span class="opacity-80 mx-1">:</span>
                                    <span class="font-bold">{{ $conditionStr }}</span>
                                    <input type="hidden" name="indicators[{{ $idx }}][condition]" value="{{ $conditionKey }}">

                                    <button type="button" class="ml-1 hover:text-red-300 transition-colors remove-filter-btn" title="Remove filter">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    @if(!empty($filters['indicators']))
                        <a href="{{ route('screener.index') }}" class="btn btn-ghost btn-xs text-error ml-auto">Clear All</a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Simplified & Unique Indicators Layout --}}
        <div class="bg-base-100 rounded-2xl border border-base-200 shadow-sm p-4 sm:p-6 mb-8">
            <h3 class="text-sm font-bold text-base-content/70 uppercase tracking-wider mb-5">Available Indicators</h3>
            
            {{-- Horizontal Subcategory Pills --}}
            <div class="flex flex-wrap gap-2 mb-6 border-b border-base-200 pb-5">
                @foreach($availableIndicators['Technical'] ?? [] as $subcatName => $indicators)
                    <button type="button" class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 subcat-pill @if($loop->first) bg-primary text-primary-content shadow-md scale-105 @else bg-base-200/70 text-base-content/70 hover:bg-base-300 hover:text-base-content hover:scale-105 @endif" data-subcat="{{ Str::slug($subcatName) }}">
                        {{ $subcatName }}
                    </button>
                @endforeach
            </div>

            {{-- Indicator Grids --}}
            <div class="indicator-containers relative min-h-[200px]">
                @foreach($availableIndicators['Technical'] ?? [] as $subcatName => $indicators)
                    <div class="indicator-grid transition-opacity duration-300 @if(!$loop->first) hidden opacity-0 absolute inset-0 @else opacity-100 relative @endif" data-subcat="{{ Str::slug($subcatName) }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                            @foreach($indicators as $ind)
                                <button type="button" class="flex items-center gap-3.5 px-4 py-3.5 rounded-xl border border-base-200 bg-base-50/50 hover:bg-white hover:border-primary/40 hover:shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:text-primary transition-all duration-200 text-sm text-left group add-indicator-btn" data-key="{{ $ind['key'] }}" data-label="{{ $ind['label'] }}" data-type="{{ $ind['type'] }}">
                                    <div class="w-8 h-8 rounded-full bg-base-200/50 group-hover:bg-primary/10 flex items-center justify-center shrink-0 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-base-content/40 group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    </div>
                                    <span class="truncate font-semibold">{{ $ind['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Results Data Table --}}
        @if($hasFilters)
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                Results
                <span class="badge badge-primary badge-sm">{{ $total }}</span>
            </h2>
            @if(count($results) > 0)
                <div class="bg-base-200/30 rounded-2xl border border-base-300 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr class="bg-base-200/80">
                                    @php
                                        $currentSort = $filters['sort_by'] ?? 'volume24h';
                                        $currentDir = $filters['sort_dir'] ?? 'desc';
                                        
                                        // Dynamically build columns based on active filters
                                        $indicatorCols = [];
                                        foreach ($filters['indicators'] ?? [] as $ind) {
                                            $def = \App\Services\ScreenerService::getIndicatorDefinition($ind['key'] ?? '');
                                            if ($def) {
                                                // Handle mapping keys for sorting correctly
                                                $period = current(array_filter([$def['period'] ?? null, 14]));
                                                $computedKey = match($def['type']) {
                                                    'rsi' => "rsi_{$period}",
                                                    'sma' => "sma_{$period}",
                                                    'ema' => "ema_{$period}",
                                                    'macd' => $def['field'],
                                                    'bb' => $def['field'],
                                                    'volume_sma' => "vol_ratio_{$period}",
                                                    default => $def['field'], // Basic fields
                                                };
                                                // Only add if not a basic field we already show by default
                                                if (!in_array($computedKey, ['price24hPcnt', 'highPrice24h', 'lowPrice24h', 'volume24h', 'turnover24h'])) {
                                                    $indicatorCols[$computedKey] = $def['label'];
                                                }
                                            }
                                        }
                                    @endphp
                                    @foreach([
                                        'symbol' => 'Symbol',
                                        'lastPrice' => 'Price',
                                        'price24hPcnt' => '24h Change %',
                                        'volume24h' => 'Volume',
                                        'turnover24h' => 'Turnover (USDT)',
                                    ] as $col => $label)
                                        @php
                                            $newDir = ($currentSort === $col && $currentDir === 'desc') ? 'asc' : 'desc';
                                            $sortParams = array_merge($filters, ['sort_by' => $col, 'sort_dir' => $newDir]);
                                        @endphp
                                        <th class="cursor-pointer hover:bg-base-300/50 transition-colors select-none whitespace-nowrap">
                                            <a href="{{ route('screener.index', $sortParams) }}" class="flex items-center gap-1">
                                                {{ $label }}
                                                @if($currentSort === $col)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        @if($currentDir === 'asc')
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        @endif
                                                    </svg>
                                                @endif
                                            </a>
                                        </th>
                                    @endforeach
                                    @foreach($indicatorCols as $colKey => $colLabel)
                                        @php
                                            $newDir = ($currentSort === $colKey && $currentDir === 'desc') ? 'asc' : 'desc';
                                            $sortParams = array_merge($filters, ['sort_by' => $colKey, 'sort_dir' => $newDir]);
                                        @endphp
                                        <th class="cursor-pointer hover:bg-base-300/50 transition-colors select-none whitespace-nowrap">
                                            <a href="{{ route('screener.index', $sortParams) }}" class="flex items-center gap-1 text-primary">
                                                {{ $colLabel }}
                                                @if($currentSort === $colKey)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        @if($currentDir === 'asc')
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        @endif
                                                    </svg>
                                                @endif
                                            </a>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $row)
                                    <tr class="hover:bg-base-200/40 transition-colors">
                                        <td class="font-bold whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="text-primary">{{ str_replace('USDT', '', $row['symbol']) }}</span>
                                                <span class="text-base-content/30 text-xs">USDT</span>
                                            </div>
                                        </td>
                                        <td class="font-mono whitespace-nowrap">
                                            ${{ number_format($row['lastPrice'], $row['lastPrice'] < 1 ? 6 : 2) }}
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <span @class([
                                                'font-bold text-sm',
                                                'text-success' => $row['price24hPcnt'] > 0,
                                                'text-error' => $row['price24hPcnt'] < 0,
                                                'text-base-content/50' => $row['price24hPcnt'] == 0,
                                            ])>
                                                {{ $row['price24hPcnt'] > 0 ? '+' : '' }}{{ number_format($row['price24hPcnt'], 2) }}%
                                            </span>
                                        </td>
                                        <td class="font-mono text-xs whitespace-nowrap">{{ number_format($row['volume24h'], 0) }}</td>
                                        <td class="font-mono text-xs whitespace-nowrap">${{ number_format($row['turnover24h'], 0) }}</td>
                                        @foreach($indicatorCols as $colKey => $colLabel)
                                            <td class="font-mono text-xs whitespace-nowrap">
                                                @php $val = $row['indicators'][$colKey] ?? $row[$colKey] ?? null; @endphp
                                                @if($val !== null)
                                                    @if(str_starts_with($colKey, 'rsi'))
                                                        <span @class([
                                                            'badge badge-sm font-bold',
                                                            'badge-error' => $val >= 70,
                                                            'badge-success' => $val <= 30,
                                                            'badge-ghost' => $val > 30 && $val < 70,
                                                        ])>
                                                            {{ number_format($val, 1) }}
                                                        </span>
                                                    @elseif(str_starts_with($colKey, 'vol_ratio'))
                                                        <span @class([
                                                            'badge badge-sm font-bold',
                                                            'badge-warning' => $val >= 2,
                                                            'badge-ghost' => $val < 2,
                                                        ])>
                                                            {{ number_format($val, 2) }}x
                                                        </span>
                                                    @else
                                                        {{ $val < 1 && $val > -1 && $val != 0 ? number_format($val, 6) : number_format($val, 2) }}
                                                    @endif
                                                @else
                                                    <span class="text-base-content/20">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 border-t border-base-300 text-sm text-base-content/50">
                        Showing {{ count($results) }} of {{ $total }} matching symbols • Data cached for 15 min
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-20 text-center bg-base-200/20 rounded-2xl border border-dashed border-base-300">
                    <div class="w-16 h-16 rounded-full bg-base-300/50 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-1">No matches found</h3>
                    <p class="text-base-content/50 text-sm">Adjust your filters and try scanning again.</p>
                </div>
            @endif
        @endif
    </div>

    {{-- Add Filter Modal --}}
    <dialog id="filter-modal" class="modal">
        <div class="modal-box max-w-sm rounded-2xl">
            <h3 class="font-bold text-lg mb-4" id="modal-title">Add Filter</h3>
            <form method="dialog" id="modal-form">
                <input type="hidden" id="modal-key">
                <input type="hidden" id="modal-label">
                <input type="hidden" id="modal-type">
                
                <div class="mb-6">
                    <label class="label"><span class="label-text font-bold text-xs uppercase tracking-wider">Condition</span></label>
                    <div id="modal-condition-radio-list" class="space-y-2 mt-2 max-h-[60vh] overflow-y-auto pr-2">
                        <!-- Radio buttons injected by JS -->
                    </div>
                </div>

                <div class="modal-action mt-0 flex gap-2">
                    <button type="button" class="btn btn-ghost flex-1" onclick="document.getElementById('filter-modal').close()">Cancel</button>
                    <button type="submit" class="btn btn-primary flex-1">Add to Filters</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Subcategory Pill Switching ---
        const subcatPills = document.querySelectorAll('.subcat-pill');
        const indicatorGrids = document.querySelectorAll('.indicator-grid');

        subcatPills.forEach(pill => {
            pill.addEventListener('click', () => {
                const targetSubcat = pill.dataset.subcat;

                // Update pills styling
                subcatPills.forEach(p => {
                    p.classList.remove('bg-primary', 'text-primary-content', 'shadow-md', 'scale-105');
                    p.classList.add('bg-base-200/70', 'text-base-content/70');
                });
                pill.classList.remove('bg-base-200/70', 'text-base-content/70');
                pill.classList.add('bg-primary', 'text-primary-content', 'shadow-md', 'scale-105');

                // Update grids with basic transition
                indicatorGrids.forEach(grid => {
                    if (grid.dataset.subcat === targetSubcat) {
                        grid.classList.remove('hidden', 'absolute', 'inset-0');
                        grid.classList.add('relative', 'z-10');
                        setTimeout(() => grid.classList.remove('opacity-0'), 10);
                    } else {
                        grid.classList.add('hidden', 'opacity-0', 'absolute', 'inset-0');
                        grid.classList.remove('relative', 'z-10');
                    }
                });
            });
        });

        // --- Filter Adding Logic (Modal) ---
        const filterModal = document.getElementById('filter-modal');
        const modalForm = document.getElementById('modal-form');
        const modalTitle = document.getElementById('modal-title');
        const modalKey = document.getElementById('modal-key');
        const modalLabel = document.getElementById('modal-label');
        const modalType = document.getElementById('modal-type');
        
        // Condition inputs (for all indicators now)
        const conditionRadioList = document.getElementById('modal-condition-radio-list');
        
        const activeContainer = document.getElementById('active-filters-container');
        const noFiltersMsg = document.getElementById('no-filters-msg');
        let filterIndex = document.querySelectorAll('.filter-chip').length;
        
        // Injected PHP predefined conditions
        const indicatorConditions = @json($indicatorConditions ?? []);

        // Open modal when indicator clicked
        document.querySelectorAll('.add-indicator-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.key;
                const lbl = btn.dataset.label;
                const type = btn.dataset.type;
                
                modalKey.value = key;
                modalLabel.value = lbl;
                modalType.value = type;
                modalTitle.textContent = 'Set Condition: ' + lbl;
                
                // Populate radio buttons
                conditionRadioList.innerHTML = '';
                const conditions = indicatorConditions[type] || {};
                let first = true;
                for (const [condKey, condLabel] of Object.entries(conditions)) {
                    conditionRadioList.insertAdjacentHTML('beforeend', `
                        <label class="label cursor-pointer justify-start gap-3 bg-base-200/50 hover:bg-base-200 p-3 rounded-xl border border-base-300 hover:border-primary/50 transition-colors">
                            <input type="radio" name="modal_condition_radio" value="${condKey}" class="radio radio-primary radio-sm" ${first ? 'checked' : ''} required>
                            <span class="label-text font-medium">${condLabel}</span>
                        </label>
                    `);
                    first = false;
                }
                
                filterModal.showModal();
            });
        });

        // Handle Modal Submit
        modalForm.addEventListener('submit', (e) => {
            const key = modalKey.value;
            const label = modalLabel.value;
            const type = modalType.value;
            
            // Radio buttons selected
            const selectedRadio = document.querySelector('input[name="modal_condition_radio"]:checked');
            if (!selectedRadio) { e.preventDefault(); return; } 
            
            const conditionKey = selectedRadio.value;
            const conditionLabel = indicatorConditions[type][conditionKey];
            
            const chipHtml = `
                <div class="badge badge-primary gap-1 py-3 px-3 shadow-sm filter-chip" data-index="${filterIndex}">
                    <span class="font-semibold">${label}</span>
                    <span class="opacity-80 mx-1">:</span>
                    <span class="font-bold">${conditionLabel}</span>
                    
                    <input type="hidden" name="indicators[${filterIndex}][key]" value="${key}">
                    <input type="hidden" name="indicators[${filterIndex}][condition]" value="${conditionKey}">

                    <button type="button" class="ml-1 hover:text-red-300 transition-colors remove-filter-btn" title="Remove filter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            `;

            // Insert chip
            if (noFiltersMsg) noFiltersMsg.style.display = 'none';
            activeContainer.insertAdjacentHTML('beforeend', chipHtml);
            filterIndex++;
            
            filterModal.close();
            e.preventDefault(); 
        });

        // Handle Chip Removal
        activeContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-filter-btn');
            if (btn) {
                btn.closest('.filter-chip').remove();
                if (activeContainer.querySelectorAll('.filter-chip').length === 0 && noFiltersMsg) {
                    noFiltersMsg.style.display = '';
                }
            }
        });

        // Search Filter
        const searchInput = document.getElementById('indicator-search');
        const allIndicatorGrids = document.querySelectorAll('.indicator-grid');
        
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            
            if (term === '') {
                // Restore normal grid view by auto-clicking the currently active pill
                const activePill = document.querySelector('.subcat-pill.bg-primary') || document.querySelector('.subcat-pill');
                if (activePill) activePill.click();
                
                // Make sure all buttons are restored to visible
                document.querySelectorAll('.add-indicator-btn').forEach(btn => {
                    btn.classList.remove('hidden');
                    btn.classList.add('flex');
                });
                return;
            }

            // Force override: make ALL grids visible during search
            allIndicatorGrids.forEach(grid => {
                grid.classList.remove('hidden', 'absolute', 'inset-0', 'opacity-0');
                grid.classList.add('relative', 'opacity-100');
            });

            // Filter individual buttons globally
            document.querySelectorAll('.add-indicator-btn').forEach(btn => {
                const label = btn.dataset.label.toLowerCase();
                const type = (btn.dataset.type || '').toLowerCase();
                
                if (label.includes(term) || type.includes(term)) {
                    btn.classList.remove('hidden');
                    btn.classList.add('flex');
                } else {
                    btn.classList.add('hidden');
                    btn.classList.remove('flex');
                }
            });
        });

        // Global timeframe update sync
        const globalTimeframe = document.getElementById('global-timeframe');
        const formTimeframe = document.getElementById('form-timeframe');
        globalTimeframe.addEventListener('change', () => {
            formTimeframe.value = globalTimeframe.value;
        });

        // Loading state on run
        const runBtn = document.getElementById('run-screener-btn');
        const mainForm = document.getElementById('screener-form');
        mainForm.addEventListener('submit', () => {
            runBtn.disabled = true;
            runBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Running...';
        });
    });
    </script>
</x-layouts.app>
