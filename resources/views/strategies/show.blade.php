<x-layouts.app :title="$strategy->name . ' - Tradexy'">
    <div class="max-w-6xl mx-auto px-6 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $strategy->color ?? '#3b82f6' }}">
                    </div>
                    <h1 class="text-3xl font-bold text-base-content">{{ $strategy->name }}</h1>
                    @php
                        $statusColors = [
                            'active' => 'badge-success',
                            'testing' => 'badge-warning',
                            'inactive' => 'badge-ghost',
                        ];
                        $statusRole = $statusColors[$strategy->status] ?? 'badge-ghost';
                    @endphp
                    <span class="badge {{ $statusRole }} font-semibold uppercase text-xs">{{ $strategy->status }}</span>
                </div>
                @if($strategy->description)
                    <p class="text-base-content/60 mt-2 max-w-2xl">{{ $strategy->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('strategies.edit', $strategy->id) }}" class="btn btn-primary btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                    </svg>
                    Edit Strategy
                </a>
            </div>
        </div>

        <!-- Performance Stats (Emphasized) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-base-100 rounded-xl p-5 shadow-sm border border-base-300 flex flex-col justify-center">
                <p class="text-xs text-base-content/40 font-semibold uppercase tracking-wider mb-1">Net PnL</p>
                @php
                    $pnlClass = $strategy->net_pnl > 0 ? 'text-success' : ($strategy->net_pnl < 0 ? 'text-error' : 'text-gray-600');
                @endphp
                <p class="text-2xl font-bold {{ $pnlClass }}">
                    ${{ number_format($strategy->net_pnl, 2) }}
                </p>
            </div>

            <div class="bg-base-100 rounded-xl p-5 shadow-sm border border-base-300 flex flex-col justify-center">
                <p class="text-xs text-base-content/40 font-semibold uppercase tracking-wider mb-1">Win Rate</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-base-content">{{ number_format($strategy->hit_ratio, 1) }}%</p>
                    <span class="text-xs text-base-content/40">({{ $strategy->trades_count }} trades)</span>
                </div>
            </div>

            <div class="bg-base-100 rounded-xl p-5 shadow-sm border border-base-300 flex flex-col justify-center">
                <p class="text-xs text-base-content/40 font-semibold uppercase tracking-wider mb-1">Edge Ratio</p>
                <p class="text-2xl font-bold text-base-content">{{ number_format($strategy->edge_ratio, 2) }}</p>
            </div>

            <div class="bg-base-100 rounded-xl p-5 shadow-sm border border-base-300 flex flex-col justify-center">
                <p class="text-xs text-base-content/40 font-semibold uppercase tracking-wider mb-1">Avg Win / Loss</p>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-success capitalize mb-0.5">W:
                        ${{ number_format($strategy->avg_win, 2) }}</span>
                    <span class="text-sm font-bold text-error capitalize">L:
                        ${{ number_format(abs($strategy->avg_loss), 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Specifications -->
        <div class="bg-base-100 rounded-xl border border-base-300 shadow-sm p-6 mb-8">
            <h3 class="font-bold text-lg text-base-content mb-4 border-b border-base-300 pb-2">Specs & Settings</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-y-6 gap-x-4">
                <div>
                    <h4 class="text-xs text-base-content/40 uppercase tracking-wider font-semibold mb-1">Target R:R</h4>
                    <span class="font-medium text-base-content">{{ $strategy->target_rr ?: 'N/A' }}</span>
                </div>
                <div>
                    <h4 class="text-xs text-base-content/40 uppercase tracking-wider font-semibold mb-1">Max Risk</h4>
                    <span
                        class="font-medium text-base-content">{{ $strategy->max_risk_per_trade ? rtrim(rtrim(number_format($strategy->max_risk_per_trade, 2), '0'), '.') . '%' : 'N/A' }}</span>
                </div>
                <div>
                    <h4 class="text-xs text-base-content/40 uppercase tracking-wider font-semibold mb-1">Markets</h4>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @forelse((array) $strategy->markets as $market)
                            <span class="badge badge-outline badge-sm">{{ $market }}</span>
                        @empty
                            <span class="text-sm text-gray-500">None</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <h4 class="text-xs text-base-content/40 uppercase tracking-wider font-semibold mb-1">Timeframes</h4>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @forelse((array) $strategy->timeframes as $tf)
                            <span class="badge badge-outline badge-sm">{{ $tf }}</span>
                        @empty
                            <span class="text-sm text-gray-500">None</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Rules Layout -->
        <h3 class="font-bold text-xl text-base-content mb-4">Trading Rules</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            @php
                $rulesGrouped = $strategy->rules->groupBy('type');
            @endphp

            <!-- Entry Rules -->
            <div class="bg-success/10 rounded-xl p-6 border border-success/20">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-1.5 bg-success/20 text-success rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-success">Entry Rules</h4>
                </div>
                <ul class="space-y-3">
                    @forelse($rulesGrouped->get('entry', []) as $rule)
                        <li class="flex items-start gap-2 text-base-content/80 text-sm">
                            <span class="mt-1 flex-shrink-0 w-1.5 h-1.5 rounded-full bg-success"></span>
                            <span>{{ $rule->rule }}</span>
                        </li>
                    @empty
                        <span class="text-sm text-success italic">No entry rules specified.</span>
                    @endforelse
                </ul>
            </div>

            <!-- Exit Rules -->
            <div class="bg-error/10 rounded-xl p-6 border border-error/20">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-1.5 bg-error/20 text-error rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-error">Exit Rules</h4>
                </div>
                <ul class="space-y-3">
                    @forelse($rulesGrouped->get('exit', []) as $rule)
                        <li class="flex items-start gap-2 text-base-content/80 text-sm">
                            <span class="mt-1 flex-shrink-0 w-1.5 h-1.5 rounded-full bg-error"></span>
                            <span>{{ $rule->rule }}</span>
                        </li>
                    @empty
                        <span class="text-sm text-error italic">No exit rules specified.</span>
                    @endforelse
                </ul>
            </div>

            <!-- Risk Management -->
            <div class="bg-warning/10 rounded-xl p-6 border border-warning/20">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-1.5 bg-warning/20 text-warning rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-warning">Risk Management</h4>
                </div>
                <ul class="space-y-3">
                    @forelse($rulesGrouped->get('risk_management', []) as $rule)
                        <li class="flex items-start gap-2 text-base-content/80 text-sm">
                            <span class="mt-1 flex-shrink-0 w-1.5 h-1.5 rounded-full bg-warning"></span>
                            <span>{{ $rule->rule }}</span>
                        </li>
                    @empty
                        <span class="text-sm text-warning italic">No risk management rules specified.</span>
                    @endforelse
                </ul>
            </div>

            <!-- Scaling -->
            <div class="bg-info/10 rounded-xl p-6 border border-info/20">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-1.5 bg-info/20 text-info rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-info">Scaling Rules</h4>
                </div>
                <ul class="space-y-3">
                    @forelse($rulesGrouped->get('scaling', []) as $rule)
                        <li class="flex items-start gap-2 text-base-content/80 text-sm">
                            <span class="mt-1 flex-shrink-0 w-1.5 h-1.5 rounded-full bg-info"></span>
                            <span>{{ $rule->rule }}</span>
                        </li>
                    @empty
                        <span class="text-sm text-info italic">No scaling rules specified.</span>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
</x-layouts.app>