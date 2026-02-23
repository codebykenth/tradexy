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
        <form action="{{ route('trades.store') }}" method="post" class="bg-gray-100 rounded-lg p-8 my-8">
            @csrf
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
                            <input type="text" class="input w-full" placeholder="BTCUSDT" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Open Trade Date Time</legend>
                            <input type="datetime-local" class="input w-full" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Close Trade Date Time</legend>
                            <input type="datetime-local" class="input w-full" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Strategy</legend>
                            <select class="select w-full">
                                <option disabled selected>Select strategy</option>
                                <option>Breakout</option>
                                <option>Breakdown</option>
                                <option>Range</option>
                            </select>
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Timeframe</legend>
                            <select class="select w-full">
                                <option disabled selected>Select timeframe</option>
                                <option>1m</option>
                                <option>5m</option>
                                <option>15m</option>
                                <option>30m</option>
                                <option>1hr</option>
                                <option>4hr</option>
                                <option>1d</option>
                            </select>
                            <!-- Put Error Here -->
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
                                <option disabled selected>Select entry side</option>
                                <option value="long">Long</option>
                                <option value="short">Short</option>
                            </select>
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Leverage (X)</legend>
                            <input type="number" placeholder="" class="input" name="leverage" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Avg Entry Price</legend>
                            <input type="number" placeholder="" class="input" name="avg_entry_price" id="avg-entry-price" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Quantity</legend>
                            <input type="number" placeholder="" class="input quantity" name="quantity" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Total Entry Val</legend>
                            <input type="number" placeholder="" class="input" disabled id="total-entry-val" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Stoploss</legend>
                            <input type="number" placeholder="" class="input" name="stop_loss_price" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Take Profit</legend>
                            <input type="number" placeholder="" class="input" name="take_profit_price" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Emotion (Entry)</legend>
                            <input type="text" placeholder="e.g. FOMO, Confident" class="input" name="entry_emotion"
                                list="entry-emotions" />

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
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>

                </div>
                <div class="w-full mt-6">
                    <fieldset class="fieldset w-full reasons-fieldset">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                            Reason for entry</legend>
                        <input type="text" placeholder="" class="input" name="type" value="entry" hidden />

                        <div class="flex items-center gap-2 w-full reason-container">
                            <input type="text" placeholder="Add reason" class="input flex-grow" name="reason[]" />
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
                        <!-- Put Error Here -->
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
                                <option disabled selected>Select exit side</option>
                                <option value="long">Long</option>
                                <option value="short">Short</option>
                            </select>
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Avg Exit Price</legend>
                            <input type="number" placeholder="" class="input" name="avg_exit_price" id="avg-exit-price" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Closed Size</legend>
                            <input type="number" placeholder="" class="input" disabled id="closed-size" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Total Exit Val</legend>
                            <input type="number" placeholder="" class="input" disabled id="total-exit-val" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Emotion (Exit)</legend>
                            <input type="text" placeholder="e.g. FOMO, Confident" class="input" name="exit_emotion"
                                list="exit-emotions" />

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
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>

                </div>
                <div class="w-full mt-6">
                    <fieldset class="fieldset w-full reasons-fieldset">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                            Reason for exit</legend>
                        <input type="text" placeholder="" class="input" name="type" value="exit" hidden />

                        <div class="flex items-center gap-2 w-full reason-container">
                            <input type="text" placeholder="Add reason" class="input flex-grow" name="reason[]" />
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
                        <!-- Put Error Here -->
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
                            <input type="number" placeholder="" class="input" name="open_fees" id="open-fees" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Close Fee</legend>
                            <input type="number" placeholder="" class="input" name="close_fees" id="close-fees" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Gross Pnl</legend>
                            <input type="number" placeholder="" class="input" name="closed_pnl" disabled id="gross-pnl" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Net Pnl (Total)</legend>
                            <input type="number" placeholder="" class="input" name="total_pnl" disabled id="total-pnl" />
                            <!-- Put Error Here -->
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
                            <!-- Put Error Here -->
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
                                    name="lesson[]" />
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
                            <!-- Put Error Here -->
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