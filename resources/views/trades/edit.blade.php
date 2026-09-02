<x-layouts.app :title="'Edit ' . $trade->symbol . ' - Tradexy'">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between my-4">
            <div>
                <x-page-title title="Edit Trade" subtitle="Update trade details and strategy." />
            </div>
        </div>

        <!-- Market Display -->
        <div class="flex items-center gap-2 mb-2">
            <div
                class="inline-flex items-center gap-2 rounded-lg bg-base-300 p-1 text-sm font-bold uppercase tracking-wider">
                @if(($trade->market ?? 'crypto') === 'pse')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-500">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.315 48.315 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                    PSE
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-orange-500">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    </svg>
                    Crypto
                @endif
            </div>
            <span class="text-xs text-gray-400">Market type cannot be changed after creation.</span>
        </div>

        <form id="form" action="{{ route('trades.update', $trade->id) }}" method="post" enctype="multipart/form-data"
            class="bg-base-200 rounded-lg p-8 my-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="market" id="market-input"
                value="{{ old('market', $trade->market ?? 'crypto') }}" />

            <x-errors />

            <!-- General Information -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-indigo-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">1</span>
                    <h2 class="text-xl font-bold text-base-content">General Information</h2>
                </div>
                <div class="grid grid-cols-12 gap-x-6 gap-y-4">
                    <div class="col-span-12 md:col-span-2">
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Symbol</legend>
                            <input type="text" class="input w-full @error('symbol') input-error @enderror"
                                placeholder="BTCUSDT" name="symbol" value="{{ old('symbol', $trade->symbol ?? '') }}"
                                id="symbol-input" />
                            @error('symbol') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Open Trade Date Time</legend>
                            <input type="datetime-local" step="1"
                                class="input w-full @error('open_datetime') input-error @enderror" name="open_datetime"
                                value="{{ old('open_datetime', $trade->open_datetime ?? '') }}" />
                            @error('open_datetime') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Close Trade Date Time</legend>
                            <input type="datetime-local" step="1"
                                class="input w-full @error('close_datetime') input-error @enderror"
                                name="close_datetime"
                                value="{{ old('close_datetime', $trade->close_datetime ?? '') }}" />
                            @error('close_datetime') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend w-full flex justify-between items-center uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                <span>Strategy</span>
                                <a href="{{ route('strategies.create') }}" target="_blank"
                                    class="text-[10px] text-blue-600 dark:text-blue-400 font-bold hover:underline normal-case">
                                    + ADD NEW
                                </a>
                            </legend>
                            <select class="select w-full" name="strategy_id">
                                <option disabled @if(!old('strategy_id')) selected @endif>Select strategy</option>
                                @foreach ($strategies as $strategy)
                                    <option value="{{$strategy->id}}" @selected(old('strategy_id', $trade->strategy_id) == $strategy->id)>
                                        {{ $strategy->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('strategy') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div class="col-span-12 md:col-span-3">
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Timeframe</legend>
                            @php
                                $currentTimeframe = old('timeframe', $trade->timeframe ?? null);
                                $standardTfs = ['1s', '1m', '2m', '3m', '5m', '10m', '15m', '30m', '45m', '1h', '2h', '3h', '4h', '6h', '8h', '12h', '1d', '2d', '3d', '1w', '1M'];
                            @endphp
                            <select class="select w-full @error('timeframe') input-error @enderror" name="timeframe" id="timeframe-select">
                                <option disabled @if(!$currentTimeframe) selected @endif value="">
                                    Select a timeframe
                                </option>
                                @if(!empty($currentTimeframe) && !in_array($currentTimeframe, $standardTfs))
                                    <option value="{{ $currentTimeframe }}" selected>{{ $currentTimeframe }} (Custom)</option>
                                @endif
                                <optgroup label="Minutes" class="crypto-timeframe">
                                    @foreach(['1s', '1m', '2m', '3m', '5m', '10m', '15m', '30m', '45m'] as $tf)
                                        <option value="{{ $tf }}" @selected($currentTimeframe === $tf)>{{ $tf }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Hours" class="crypto-timeframe">
                                    @foreach(['1h', '2h', '3h', '4h', '6h', '8h', '12h'] as $tf)
                                        <option value="{{ $tf }}" @selected($currentTimeframe === $tf)>{{ $tf }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Days & Higher">
                                    @foreach(['1d', '2d', '3d', '1w', '1M'] as $tf)
                                        <option value="{{ $tf }}" @selected($currentTimeframe === $tf)>{{ $tf }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('timeframe') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div class="col-span-12 md:col-span-4 self-end">
                        <fieldset class="fieldset w-full">
                             <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Trade Type</legend>
                            <label class="label cursor-pointer justify-start gap-4 h-12">
                                <input type="hidden" name="is_demo" value="0">
                                <input type="checkbox" name="is_demo" value="1" class="checkbox checkbox-warning" {{ old('is_demo', $trade->is_demo) ? 'checked' : '' }} />
                                <span class="label-text font-bold text-warning uppercase">Demo Trade</span>
                            </label>
                        </fieldset>
                    </div>
                </div>
            </div>
            <!-- Entry Details -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-green-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">2</span>
                    <h2 class="text-xl font-bold text-base-content">Entry Details</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    <!-- Entry Side (Crypto only) -->
                    <div class="crypto-only-field">
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Entry Side</legend>
                            <select class="select w-full @error('entry_side') input-error @enderror" name="entry_side"
                                id="entry-side">
                                <option disabled selected value="{{ old('entry_side', $trade->entry_side ?? null) }}">
                                    {{ $trade->entry_side ? ucfirst($trade->entry_side) : 'Select entry side' }}
                                </option>
                                <option value="long">Long</option>
                                <option value="short">Short</option>
                            </select>
                            @error('entry_side') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <!-- Leverage (Crypto only) -->
                    <div class="crypto-only-field">
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Leverage (X)</legend>
                            <input type="number" placeholder=""
                                class="input bg-base-300 text-base-content/50 cursor-not-allowed" name="leverage"
                                value="{{ old('leverage', $trade->leverage ?? null) }}" readonly />
                            @error('leverage') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Avg Entry Price</legend>
                            <input type="number" step="any" placeholder=""
                                class="input w-full @error('avg_entry_price') input-error @enderror"
                                name="avg_entry_price"
                                value="{{ old('avg_entry_price', $trade->avg_entry_price ?? null) }}"
                                id="avg-entry-price" />
                            @error('avg_entry_price') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Quantity</legend>
                            <input type="number" step="any" placeholder=""
                                class="input quantity w-full @error('quantity') input-error @enderror" name="quantity"
                                value="{{ old('quantity', $trade->quantity ?? null) }}" />
                            @error('quantity') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Total Entry Val</legend>
                            <input type="number" placeholder=""
                                class="input bg-base-300 text-base-content/50 cursor-not-allowed" readonly id="total-entry-val"
                                name="cum_entry_value" value="{{ old('cum_entry_value', $trade->cum_entry_value) }}" />
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Stoploss</legend>
                            <input type="number" step="any" placeholder=""
                                class="input w-full @error('stop_loss_price') input-error @enderror"
                                name="stop_loss_price"
                                value="{{ old('stop_loss_price', $trade->stop_loss_price ?? null) }}" />
                            @error('stop_loss_price') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Take Profit</legend>
                            <input type="number" step="any" placeholder=""
                                class="input w-full @error('take_profit_price') input-error @enderror"
                                name="take_profit_price"
                                value="{{ old('take_profit_price', $trade->take_profit_price ?? null) }}" />
                            @error('take_profit_price') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Emotion (Entry)</legend>
                            <input type="text" placeholder="e.g. FOMO, Confident"
                                class="input w-full @error('entry_emotion') input-error @enderror" name="entry_emotion"
                                list="entry-emotions"
                                value="{{ old('entry_emotion', $trade->entry_emotion ?? null) }}" />

                            <datalist id="entry-emotions">
                                <option value="Confident">
                                <option value="Anxious">
                                <option value="Fearful">
                                <option value="Greedy">
                                <option value="Neutral">
                                <option value="Excited">
                                <option value="Bored">
                                <option value="Revenge"></option>
                            </datalist>
                            @error('entry_emotion') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>

                </div>
                <div class="w-full mt-6">
                    <fieldset class="fieldset w-full reasons-fieldset">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                            Reason for entry</legend>

                        @php $entryReasons = $trade->reasons->where('type', 'entry')->values(); @endphp

                        @forelse ($entryReasons as $reason)
                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="Add reason"
                                    class="input flex-grow @error('entry_reason.' . $loop->index) input-error @enderror"
                                    name="entry_reason[]"
                                    value="{{ old('entry_reason.' . $loop->index, $reason->reason) }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Reason">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            @error('entry_reason.' . $loop->index) <span
                            class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        @empty
                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="Add reason"
                                    class="input flex-grow @error('entry_reason.0') input-error @enderror"
                                    name="entry_reason[]" value="{{ old('entry_reason.0') }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Reason">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            @error('entry_reason.0') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        @endforelse

                        <button type="button"
                            class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add Another
                            Reason</button>
                    </fieldset>
                </div>
            </div>
            <!-- Exit Details -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-red-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">3</span>
                    <h2 class="text-xl font-bold text-base-content">Exit Details</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Exit Side (Crypto only) -->
                    <div class="crypto-only-field">
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Exit Side</legend>
                            <select class="select w-full @error('exit_side') input-error @enderror" name="exit_side"
                                id="exit-side">
                                <option disabled selected value="{{ old('exit_side', $trade->exit_side ?? null) }}">
                                    {{ ucfirst($trade->exit_side) }}
                                </option>
                                <option value="long">Long</option>
                                <option value="short">Short</option>
                            </select>
                            @error('exit_side') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Avg Exit Price</legend>
                            <input type="number" step="any" placeholder=""
                                class="input w-full @error('avg_exit_price') input-error @enderror"
                                name="avg_exit_price"
                                value="{{ old('avg_exit_price', $trade->avg_exit_price ?? null) }}"
                                id="avg-exit-price" />
                            @error('avg_exit_price') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Closed Size</legend>
                            <input type="number" placeholder="" class="input w-full" disabled
                                value="{{ $trade->quantity }}" id="closed-size" />
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Total Exit Val</legend>
                            <input type="number" placeholder="" class="input w-full" disabled
                                value="{{ $trade->quantity * $trade->avg_exit_price }}" id="total-exit-val" />
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Emotion (Exit)</legend>
                            <input type="text" placeholder="e.g. FOMO, Confident"
                                class="input w-full @error('exit_emotion') input-error @enderror" name="exit_emotion"
                                list="exit-emotions" value="{{ old('exit_emotion', $trade->exit_emotion ?? null) }}" />

                            <datalist id="exit-emotions">
                                <option value="Confident">
                                <option value="Anxious">
                                <option value="Fearful">
                                <option value="Greedy">
                                <option value="Neutral">
                                <option value="Excited">
                                <option value="Bored">
                                <option value="Revenge"></option>
                            </datalist>
                            @error('exit_emotion') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>

                </div>
                <div class="w-full mt-6">
                    <fieldset class="fieldset w-full reasons-fieldset">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                            Reason for exit</legend>

                        @php $exitReasons = $trade->reasons->where('type', 'exit')->values(); @endphp

                        @forelse ($exitReasons as $reason)
                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="Add reason"
                                    class="input flex-grow @error('exit_reason.' . $loop->index) input-error @enderror"
                                    name="exit_reason[]"
                                    value="{{ old('exit_reason.' . $loop->index, $reason->reason) }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Reason">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            @error('exit_reason.' . $loop->index) <span
                            class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        @empty
                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="Add reason"
                                    class="input flex-grow @error('exit_reason.0') input-error @enderror"
                                    name="exit_reason[]" value="{{ old('exit_reason.0') }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Reason">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            @error('exit_reason.0') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        @endforelse

                        <button type="button"
                            class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add Another
                            Reason</button>
                    </fieldset>
                </div>
            </div>
            <!-- Pnl Fees -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-blue-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">4</span>
                    <h2 class="text-xl font-bold text-base-content">PnL & Fees</h2>
                </div>

                <!-- Crypto Fees -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 crypto-only-field">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Open Fee</legend>
                            <input type="number" step="any" placeholder=""
                                class="input w-full @error('open_fees') input-error @enderror" name="open_fees"
                                value="{{ old('open_fees', $trade->open_fees ?? null) }}" id="open-fees" />
                            @error('open_fees') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Close Fee</legend>
                            <input type="number" step="any" placeholder=""
                                class="input w-full @error('close_fees') input-error @enderror" name="close_fees"
                                value="{{ old('close_fees', $trade->close_fees ?? null) }}" id="close-fees" />
                            @error('close_fees') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                </div>

                <!-- PSE Fees (auto-calculated from MyTrade rates) -->
                <div class="pse-only-field">
                    <p class="text-xs text-base-content/40 mb-3">Fees auto-calculated based on MyTrade rates. You can override
                        values manually.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6 pse-only-field">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Commission (0.25%)</legend>
                            <input type="number" step="any" placeholder="" class="input pse-fee-input"
                                name="broker_commission" id="pse-broker-commission"
                                value="{{ old('broker_commission', $trade->broker_commission ?? null) }}" />
                            @error('broker_commission') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                PSE Trans (0.005%)</legend>
                            <input type="number" step="any" placeholder="" class="input pse-fee-input"
                                name="pse_trans_fee" id="pse-trans-fee"
                                value="{{ old('pse_trans_fee', $trade->pse_trans_fee ?? null) }}" />
                            @error('pse_trans_fee') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                SCCP (0.01%)</legend>
                            <input type="number" step="any" placeholder="" class="input pse-fee-input" name="sccp_fee"
                                id="pse-sccp-fee" value="{{ old('sccp_fee', $trade->sccp_fee ?? null) }}" />
                            @error('sccp_fee') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                VAT (12% of Comm)</legend>
                            <input type="number" step="any" placeholder="" class="input pse-fee-input" name="pse_vat"
                                id="pse-vat" value="{{ old('pse_vat', $trade->pse_vat ?? null) }}" />
                            @error('pse_vat') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Sales Tax (0.1% Sell)</legend>
                            <input type="number" step="any" placeholder="" class="input pse-fee-input" name="sales_tax"
                                id="pse-sales-tax" value="{{ old('sales_tax', $trade->sales_tax ?? null) }}" />
                            @error('sales_tax') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                </div>

                <!-- PnL Summary (shared) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Gross Pnl</legend>
                            <input type="number" placeholder="" class="input w-full" name="closed_pnl" disabled
                                value="{{ $trade->closed_pnl }}" id="gross-pnl" />
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Net Pnl (Total)</legend>
                            <input type="number" placeholder="" class="input w-full" name="total_pnl" disabled
                                value="{{ $trade->total_pnl }}" id="total-pnl" />
                        </fieldset>
                    </div>
                </div>
            </div>
            <!-- Analysis & Media -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-purple-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">5</span>
                    <h2 class="text-xl font-bold text-base-content">Analysis & Media</h2>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Chart Screenshot</legend>
                            @include('trades.partials.chart-upload')
                            @error('chart_picture') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div class="w-full">
                        <fieldset class="fieldset w-full reasons-fieldset">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Lesson Learned</legend>
                            @php $existingLessons = $trade->lessons->values(); @endphp

                            @forelse ($existingLessons as $lesson)
                                <div class="flex items-center gap-2 w-full reason-container">
                                    <input type="text" placeholder="What did you learn?"
                                        class="input flex-grow @error('lesson.' . $loop->index) input-error @enderror"
                                        name="lesson[]" value="{{ old('lesson.' . $loop->index, $lesson->lesson) }}" />
                                    <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                        aria-label="Delete Reason">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                                @error('lesson.' . $loop->index) <span class="text-error mt-1 text-sm">{{ $message }}</span>
                                @enderror
                            @empty
                                <div class="flex items-center gap-2 w-full reason-container">
                                    <input type="text" placeholder="What did you learn?"
                                        class="input flex-grow @error('lesson.0') input-error @enderror" name="lesson[]"
                                        value="{{ old('lesson.0') }}" />
                                    <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                        aria-label="Delete Reason">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                                @error('lesson.0') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                            @endforelse

                            <button type="button"
                                class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add
                                Another
                                Reason</button>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-8 justify-end">
                <button class="btn btn-primary" type="submit">Save Trade</button>
            </div>
        </form>
    </div>
    @include('trades.partials.form-scripts')
    @include('trades.partials.market-toggle-script')
    @include('components.form-dirty-state-check')
</x-layouts.app>