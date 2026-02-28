<x-layouts.app>
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between my-4">
            <div>
                <h1 class="text-2xl font-bold">Add Trade</h1>
                <p>Manually record a closed position or backfill trade history.</p>
            </div>
            <div>
                <a href="{{ route('trades.index') }}"><- Back to trades</a>
            </div>
        </div>
        <form id="form" action="{{ route('trades.store') }}" enctype="multipart/form-data" method="post"
            class="bg-gray-100 rounded-lg p-8 my-8">
            @csrf
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
                                placeholder="BTCUSDT" name="symbol" value="{{ old('symbol') }}" />
                            @error('symbol') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Open Trade Date Time</legend>
                            <input type="datetime-local"
                                class="input w-full @error('open_datetime') input-error @enderror" name="open_datetime"
                                value="{{ old('open_datetime') }}" />
                            @error('open_datetime') <span class="text-error text-xs mt-1">{{ $message }}</span>
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
                                name="close_datetime" value="{{ old('close_datetime') }}" />
                            @error('close_datetime') <span class="text-error text-xs mt-1">{{ $message }}</span>
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
                                <option value="1" @selected(old('strategy_id') == 'Breakout')>Breakout</option>
                                <option value="2" @selected(old('strategy_id') == 'Breakdown')>Breakdown</option>
                                <option value="3" @selected(old('strategy_id') == 'Range')>Range</option>
                            </select>
                            @error('strategy_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Timeframe</legend>
                            <select class="select w-full" name="timeframe">
                                <option disabled @if(!old('timeframe')) selected @endif>Select timeframe</option>
                                <option value="1m" @selected(old('timeframe') == '1m')>1m</option>
                                <option value="5m" @selected(old('timeframe') == '5m')>5m</option>
                                <option value="15m" @selected(old('timeframe') == '15m')>15m</option>
                                <option value="30m" @selected(old('timeframe') == '30m')>30m</option>
                                <option value="1hr" @selected(old('timeframe') == '1hr')>1hr</option>
                                <option value="4hr" @selected(old('timeframe') == '4hr')>4hr</option>
                                <option value="1d" @selected(old('timeframe') == '1d')>1d</option>
                            </select>
                            @error('timeframe') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
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
                            <select class="select w-full" name="entry_side" id="entry-side">
                                <option disabled @if(!old('entry_side')) selected @endif>Select entry side</option>
                                <option value="long" @selected(old('entry_side') == 'long')>Long</option>
                                <option value="short" @selected(old('entry_side') == 'short')>Short</option>
                            </select>
                            @error('entry_side') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Leverage (X)</legend>
                            <input type="number" placeholder="" class="input" name="leverage"
                                value="{{ old('leverage') }}" />
                            @error('leverage') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Avg Entry Price</legend>
                            <input type="number" placeholder="" class="input" name="avg_entry_price"
                                id="avg-entry-price" value="{{ old('avg_entry_price') }}" />
                            @error('avg_entry_price') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Quantity</legend>
                            <input type="number" placeholder="" class="input quantity" name="quantity"
                                value="{{ old('quantity') }}" />
                            @error('quantity') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Total Entry Val</legend>
                            <input type="number" placeholder=""
                                class="input bg-gray-200 text-gray-500 cursor-not-allowed" readonly id="total-entry-val"
                                name="cum_entry_value" value="{{ old('cum_entry_value') }}" />
                            @error('cum_entry_value') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Stoploss</legend>
                            <input type="number" placeholder="" class="input" name="stop_loss_price"
                                value="{{ old('stop_loss_price') }}" />
                            @error('stop_loss_price') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Take Profit</legend>
                            <input type="number" placeholder="" class="input" name="take_profit_price"
                                value="{{ old('take_profit_price') }}" />
                            @error('take_profit_price') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Emotion (Entry)</legend>
                            <input type="text" placeholder="e.g. FOMO, Confident" class="input" name="entry_emotion"
                                list="entry-emotions" value="{{ old('entry_emotion') }}" />

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
                            @error('entry_emotion') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>

                </div>
                <div class="w-full mt-6">
                    <fieldset class="fieldset w-full reasons-fieldset">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                            Reason for entry</legend>
                        <!-- <input type="text" placeholder="" class="input" name="type" value="entry" hidden /> -->

                        <div class="flex items-center gap-2 w-full reason-container">
                            <input type="text" placeholder="Add reason" class="input flex-grow" name="entry_reason[]"
                                value="{{ old('entry_reason.0') }}" />
                            <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                aria-label="Delete Reason">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                        <button type="button"
                            class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add Another
                            Reason</button>
                        @error('entry_reason') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        @error('entry_reason.*') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
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
                            <select class="select w-full" name="exit_side" id="exit-side">
                                <option disabled @if(!old('exit_side')) selected @endif>Select exit side</option>
                                <option value="long" @selected(old('exit_side') == 'long')>Long</option>
                                <option value="short" @selected(old('exit_side') == 'short')>Short</option>
                            </select>
                            @error('exit_side') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Avg Exit Price</legend>
                            <input type="number" placeholder="" class="input" name="avg_exit_price" id="avg-exit-price"
                                value="{{ old('avg_exit_price') }}" />
                            @error('avg_exit_price') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Closed Size</legend>
                            <input type="number" placeholder=""
                                class="input bg-gray-200 text-gray-500 cursor-not-allowed" readonly id="closed-size" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Total Exit Val</legend>
                            <input type="number" placeholder=""
                                class="input bg-gray-200 text-gray-500 cursor-not-allowed" readonly id="total-exit-val"
                                name="cum_exit_value" value="{{ old('cum_exit_value') }}" />
                            @error('cum_exit_value') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Emotion (Exit)</legend>
                            <input type="text" placeholder="e.g. FOMO, Confident" class="input" name="exit_emotion"
                                list="exit-emotions" value="{{ old('exit_emotion') }}" />

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
                            @error('exit_emotion') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>

                </div>
                <div class="w-full mt-6">
                    <fieldset class="fieldset w-full reasons-fieldset">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                            Reason for exit</legend>
                        <!-- <input type="text" placeholder="" class="input" name="type" value="exit" hidden /> -->

                        <div class="flex items-center gap-2 w-full reason-container">
                            <input type="text" placeholder="Add reason" class="input flex-grow" name="exit_reason[]"
                                value="{{ old('exit_reason.0') }}" />
                            <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                aria-label="Delete Reason">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                        <button type="button"
                            class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add Another
                            Reason</button>
                        @error('exit_reason') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        @error('exit_reason.*') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
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
                            <input type="number" placeholder="" class="input" name="open_fees" id="open-fees"
                                value="{{ old('open_fees') }}" />
                            @error('open_fees') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Close Fee</legend>
                            <input type="number" placeholder="" class="input" name="close_fees" id="close-fees"
                                value="{{ old('close_fees') }}" />
                            @error('close_fees') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Gross Pnl</legend>
                            <input type="number" placeholder=""
                                class="input bg-gray-200 text-gray-500 cursor-not-allowed" name="closed_pnl" readonly
                                id="gross-pnl" value="{{ old('closed_pnl') }}" />
                            @error('closed_pnl') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Net Pnl (Total)</legend>
                            <input type="number" placeholder=""
                                class="input bg-gray-200 text-gray-500 cursor-not-allowed" name="total_pnl" readonly
                                id="total-pnl" value="{{ old('total_pnl') }}" />
                            @error('total_pnl') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
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
                            @error('chart_picture') <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                    <div class="w-full">
                        <fieldset class="fieldset w-full reasons-fieldset">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Lesson Learned</legend>
                            <input type="text" placeholder="" class="input" name="type" value="entry" hidden />

                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="What did you learn?" class="input flex-grow"
                                    name="lesson[]" value="{{ old('lesson.0') }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Reason">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            <button type="button"
                                class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add
                                Another
                                Reason</button>
                            @error('lesson') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                            @error('lesson.*') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
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