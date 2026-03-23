<x-layouts.app title="Create Strategy - Tradexy">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between my-4">
            <div>
                <x-page-title title="Add New Strategy"
                    subtitle="Define a new trading strategy to assign to your trades." />
            </div>
        </div>
        <form action="{{ route('strategies.store') }}" method="post" class="bg-base-200 rounded-lg p-8 my-8" id="form">
            @csrf
            <x-errors />

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Name -->
                <div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                            Strategy Name*
                        </legend>
                        <input type="text" class="input w-full @error('name') input-error @enderror" name="name"
                            value="{{ old('name') }}" placeholder="e.g. Breakout" />
                        @error('name') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                    </fieldset>
                </div>

                <!-- Status -->
                <div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                            Status
                        </legend>
                        <select class="select w-full @error('status') input-error @enderror" name="status">
                            <option value="active" @selected(old('status') == 'active')>Active</option>
                            <option value="testing" @selected(old('status') == 'testing')>Testing</option>
                            <option value="inactive" @selected(old('status') == 'inactive')>Inactive</option>
                        </select>
                        @error('status') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                    </fieldset>
                </div>

                <!-- Target R:R -->
                <div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                            Target R:R
                        </legend>
                        <input type="number" step="0.01" class="input w-full @error('target_rr') input-error @enderror"
                            name="target_rr" value="{{ old('target_rr') }}" placeholder="e.g. 2.5" />
                        @error('target_rr') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                    </fieldset>
                </div>

                <!-- Max Risk Per Trade -->
                <div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                            Max Risk Per Trade (%)
                        </legend>
                        <input type="number" step="0.01"
                            class="input w-full @error('max_risk_per_trade') input-error @enderror"
                            name="max_risk_per_trade" value="{{ old('max_risk_per_trade') }}" placeholder="e.g. 1.0" />
                        @error('max_risk_per_trade') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                        @enderror
                    </fieldset>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                <!-- Color & Category -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Strategy Color
                            </legend>
                            <input type="color" class="input p-1 h-12 w-full @error('color') input-error @enderror" name="color"
                                value="{{ old('color', '#6366f1') }}" />
                            @error('color') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                                Type / Category
                            </legend>
                            <select class="select w-full" name="category[]">
                                <option value="Day Trade" @selected(collect(old('category'))->contains('Day Trade'))>Day Trade</option>
                                <option value="Swing Trade" @selected(collect(old('category'))->contains('Swing Trade'))>Swing Trade</option>
                                <option value="Scalping" @selected(collect(old('category'))->contains('Scalping'))>Scalping</option>
                                <option value="Position" @selected(collect(old('category'))->contains('Position'))>Position</option>
                                <option value="Investment" @selected(collect(old('category'))->contains('Investment'))>Investment</option>
                            </select>
                        </fieldset>
                    </div>
                </div>

                <!-- Markets & Timeframes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <legend class="fieldset-legend uppercase font-semibold text-[10px] tracking-wider text-base-content/30 mb-2">
                            Markets
                        </legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['crypto', 'pse', 'forex', 'stocks', 'indices', 'commodities'] as $m)
                                <label class="label cursor-pointer gap-2 bg-base-100 px-3 py-1.5 rounded-lg border border-base-300">
                                    <input type="checkbox" name="markets[]" value="{{ $m }}" @checked(collect(old('markets'))->contains($m)) class="checkbox checkbox-sm checkbox-primary" />
                                    <span class="label-text uppercase text-[10px] font-black tracking-tighter">{{ $m }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <legend class="fieldset-legend uppercase font-semibold text-[10px] tracking-wider text-base-content/30 mb-2">
                            Timeframes
                        </legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['1m', '5m', '15m', '1h', '4h', '1d', '1w'] as $tf)
                                <label class="label cursor-pointer gap-2 bg-base-100 px-3 py-1.5 rounded-lg border border-base-300">
                                    <input type="checkbox" name="timeframes[]" value="{{ $tf }}" @checked(collect(old('timeframes'))->contains($tf)) class="checkbox checkbox-sm checkbox-primary" />
                                    <span class="label-text uppercase text-[10px] font-black tracking-tighter">{{ $tf }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/50">
                        Description
                    </legend>
                    <textarea class="textarea w-full @error('description') input-error @enderror" name="description"
                        rows="3"
                        placeholder="Briefly describe the rules of this strategy...">{{ old('description') }}</textarea>
                    @error('description') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                </fieldset>
            </div>

            <!-- Strategy Rules Section -->
            <div class="mb-10 mt-8">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-indigo-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">Rules</span>
                    <h2 class="text-xl font-bold text-base-content">Define Strategy Rules</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Entry Rules -->
                    <div class="w-full">
                        <fieldset class="fieldset w-full reasons-fieldset">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-green-600">
                                Entry Rules
                            </legend>

                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="e.g. Price must close above 50 EMA"
                                    class="input flex-grow" name="entry_rules[]" value="{{ old('entry_rules.0') }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Rule">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            <button type="button"
                                class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add Entry
                                Rule</button>
                            @error('entry_rules') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                            @error('entry_rules.*') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>

                    <!-- Exit Rules -->
                    <div class="w-full">
                        <fieldset class="fieldset w-full reasons-fieldset">
                            <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-red-600">
                                Exit Rules
                            </legend>

                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="e.g. Stop loss hit or trailing triggered"
                                    class="input flex-grow" name="exit_rules[]" value="{{ old('exit_rules.0') }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Rule">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            <button type="button"
                                class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add Exit
                                Rule</button>
                            @error('exit_rules') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                            @error('exit_rules.*') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>

                    <!-- Risk Management Rules -->
                    <div class="w-full">
                        <fieldset class="fieldset w-full reasons-fieldset">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-orange-600">
                                Risk Management Rules
                            </legend>

                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="e.g. Move SL to breakeven at 1R" class="input flex-grow"
                                    name="risk_management_rules[]" value="{{ old('risk_management_rules.0') }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Rule">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            <button type="button"
                                class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add Risk
                                Management
                                Rule</button>
                            @error('risk_management_rules') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                            @error('risk_management_rules.*') <span
                            class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                        </fieldset>
                    </div>

                    <!-- Scaling Rules -->
                    <div class="w-full">
                        <fieldset class="fieldset w-full reasons-fieldset">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-blue-600">
                                Scaling Rules
                            </legend>

                            <div class="flex items-center gap-2 w-full reason-container">
                                <input type="text" placeholder="e.g. Take 50% profit at 2R" class="input flex-grow"
                                    name="scaling_rules[]" value="{{ old('scaling_rules.0') }}" />
                                <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                    aria-label="Delete Rule">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            <button type="button"
                                class="text-left cursor-pointer mt-2 text-primary font-bold add-reason-btn">+ Add
                                Scaling
                                Rule</button>
                            @error('scaling_rules') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                            @error('scaling_rules.*') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                            @enderror
                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-8 justify-end mt-8">
                <button class="btn btn-primary" type="submit">Save Strategy</button>
            </div>
        </form>
    </div>
</x-layouts.app>

@include('components.dynamic-repeater-script')
@include('components.form-dirty-state-check')