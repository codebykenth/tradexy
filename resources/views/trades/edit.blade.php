<x-layouts.app>
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between my-4">
            <div>
                <h1 class="text-2xl font-bold">Edit Trade</h1>
                <p>Update trade details and strategy.</p>
            </div>
            <div>
                <a href="{{ route('trades.index') }}"><- Back to trades</a>
            </div>
        </div>
        <form id="form" action="{{ route('trades.update', $trade->id) }}" method="post" enctype="multipart/form-data"
            class="bg-gray-100 rounded-lg p-8 my-8">
            @csrf
            @method('PUT')

            <x-errors />

            <!-- General Information -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-indigo-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">1</span>
                    <h2 class="text-xl font-bold text-gray-900">General Information</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Symbol</legend>
                            <input type="text" class="input w-full @error('symbol') input-error @enderror"
                                placeholder="BTCUSDT" name="symbol" value="{{ old('symbol', $trade->symbol ?? '') }}" />
                            @error('symbol') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Open Trade Date Time</legend>
                            <input type="datetime-local"
                                class="input w-full @error('open_datetime') input-error @enderror" name="open_datetime"
                                value="{{ old('open_datetime', $trade->open_datetime ?? '') }}" />
                            @error('open_datetime') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Close Trade Date Time</legend>
                            <input type="datetime-local"
                                class="input w-full @error('close_datetime') input-error @enderror"
                                name="close_datetime"
                                value="{{ old('close_datetime', $trade->close_datetime ?? '') }}" />
                            @error('close_datetime') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Strategy</legend>
                            <select class="select w-full" name="strategy_id">
                                <option disabled @if(!old('strategy_id')) selected @endif>Select strategy</option>
                                @if($strategies)
                                    @foreach ($strategies as $strategy)
                                        <option value="{{$strategy->id}}" @selected(old('strategy_id', $trade->strategy_id) == $strategy->id)>
                                            {{ $strategy->name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="1" @selected(old('strategy_id', $trade->strategy_id) == 1)>Breakout
                                    </option>
                                    <option value="2" @selected(old('strategy_id', $trade->strategy_id) == 2)>Breakdown
                                    </option>
                                    <option value="3" @selected(old('strategy_id', $trade->strategy_id) == 3)>Range</option>
                                @endif
                            </select>
                            @error('strategy') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Timeframe</legend>
                            <select class="select w-full @error('timeframe') input-error @enderror" name="timeframe">
                                <option disabled selected value="{{ old('timeframe', $trade->timeframe ?? null) }}">
                                    {{ $trade->timeframe ?? 'Select a timeframe' }}
                                </option>
                                <option>1m</option>
                                <option>5m</option>
                                <option>15m</option>
                                <option>30m</option>
                                <option>1hr</option>
                                <option>4hr</option>
                                <option>1d</option>
                            </select>
                            @error('timeframe') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                </div>
            </div>
            <!-- Entry Details -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-green-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">2</span>
                    <h2 class="text-xl font-bold text-gray-900">Entry Details</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Leverage (X)</legend>
                            <input type="number" placeholder=""
                                class="input w-full @error('leverage') input-error @enderror" name="leverage"
                                value="{{ old('leverage', $trade->leverage ?? null) }}" />
                            @error('leverage') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Avg Entry Price</legend>
                            <input type="number" placeholder=""
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
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Quantity</legend>
                            <input type="number" placeholder=""
                                class="input quantity w-full @error('quantity') input-error @enderror" name="quantity"
                                value="{{ old('quantity', $trade->quantity ?? null) }}" />
                            @error('quantity') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Total Entry Val</legend>
                            <input type="number" placeholder="" class="input w-full" disabled
                                value="{{ $trade->quantity * $trade->avg_entry_price }}" id="total-entry-val" />
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Stoploss</legend>
                            <input type="number" placeholder=""
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
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Take Profit</legend>
                            <input type="number" placeholder=""
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
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                            {{-- Fallback: always show at least one empty input --}}
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
                    <h2 class="text-xl font-bold text-gray-900">Exit Details</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Avg Exit Price</legend>
                            <input type="number" placeholder=""
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
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Closed Size</legend>
                            <input type="number" placeholder="" class="input w-full" disabled
                                value="{{ $trade->quantity }}" id="closed-size" />
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Total Exit Val</legend>
                            <input type="number" placeholder="" class="input w-full" disabled
                                value="{{ $trade->quantity * $trade->avg_exit_price }}" id="total-exit-val" />
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                            {{-- Fallback: always show at least one empty input --}}
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
                    <h2 class="text-xl font-bold text-gray-900">PnL & Fees</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Open Fee</legend>
                            <input type="number" placeholder=""
                                class="input w-full @error('open_fees') input-error @enderror" name="open_fees"
                                value="{{ old('open_fees', $trade->open_fees ?? null) }}" id="open-fees" />
                            @error('open_fees') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Close Fee</legend>
                            <input type="number" placeholder=""
                                class="input w-full @error('close_fees') input-error @enderror" name="close_fees"
                                value="{{ old('close_fees', $trade->close_fees ?? null) }}" id="close-fees" />
                            @error('close_fees') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Gross Pnl</legend>
                            <input type="number" placeholder="" class="input w-full" name="closed_pnl" disabled
                                value="{{ $trade->closed_pnl }}" id="gross-pnl" />
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                    <h2 class="text-xl font-bold text-gray-900">Analysis & Media</h2>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Chart Screenshot</legend>
                            @include('trades.partials.chart-upload')
                            @error('chart_picture') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div class="w-full">
                        <fieldset class="fieldset w-full reasons-fieldset">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
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
                                {{-- Fallback: always show at least one empty input --}}
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
                <div>
                    <a href="{{ route('trades.index') }}">Cancel</a>
                </div>
                <button class="btn btn-primary" type="submit">Save Trade</button>
            </div>
        </form>
    </div>
</x-layouts.app>

@include('trades.partials.form-scripts')
@include('components.form-dirty-state-check')