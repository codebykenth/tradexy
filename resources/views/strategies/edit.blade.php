<x-layouts.app :title="'Edit ' . $strategy->name . ' - Tradexy'">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between my-4">
            <div>
                <x-page-title title="Edit Strategy: {{ $strategy->name }}"
                    subtitle="Update your strategy details and defined rules." />
            </div>
            <div>
                <a href="{{ route('strategies.index') }}"><- Back to strategies</a>
            </div>
        </div>
        <form action="{{ route('strategies.update', $strategy->id) }}" method="post"
            class="bg-gray-100 rounded-lg p-8 my-8" id="form">
            @csrf
            @method('PUT')
            <x-errors />

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Name -->
                <div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                            Strategy Name*
                        </legend>
                        <input type="text" class="input w-full @error('name') input-error @enderror" name="name"
                            value="{{ old('name', $strategy->name) }}" placeholder="e.g. Breakout" />
                        @error('name') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                    </fieldset>
                </div>

                <!-- Status -->
                <div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                            Status
                        </legend>
                        <select class="select w-full @error('status') input-error @enderror" name="status">
                            <option value="active" @selected(old('status', $strategy->status) == 'active')>Active</option>
                            <option value="testing" @selected(old('status', $strategy->status) == 'testing')>Testing
                            </option>
                            <option value="inactive" @selected(old('status', $strategy->status) == 'inactive')>Inactive
                            </option>
                        </select>
                        @error('status') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                    </fieldset>
                </div>

                <!-- Target R:R -->
                <div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                            Target R:R
                        </legend>
                        <input type="number" step="0.01" class="input w-full @error('target_rr') input-error @enderror"
                            name="target_rr" value="{{ old('target_rr', $strategy->target_rr) }}"
                            placeholder="e.g. 2.5" />
                        @error('target_rr') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                    </fieldset>
                </div>

                <!-- Max Risk Per Trade -->
                <div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                            Max Risk Per Trade (%)
                        </legend>
                        <input type="number" step="0.01"
                            class="input w-full @error('max_risk_per_trade') input-error @enderror"
                            name="max_risk_per_trade"
                            value="{{ old('max_risk_per_trade', $strategy->max_risk_per_trade) }}"
                            placeholder="e.g. 1.0" />
                        @error('max_risk_per_trade') <span class="text-error mt-1 text-sm">{{ $message }}</span>
                        @enderror
                    </fieldset>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                        Description
                    </legend>
                    <textarea class="textarea w-full @error('description') input-error @enderror" name="description"
                        rows="3"
                        placeholder="Briefly describe the rules of this strategy...">{{ old('description', $strategy->description) }}</textarea>
                    @error('description') <span class="text-error mt-1 text-sm">{{ $message }}</span> @enderror
                </fieldset>
            </div>

            <!-- Strategy Rules Section -->
            <div class="mb-10 mt-8">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-indigo-600 text-white rounded py-1 px-3 text-sm font-bold flex items-center justify-center">Rules</span>
                    <h2 class="text-xl font-bold text-gray-900">Define Strategy Rules</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Entry Rules -->
                    <div class="w-full">
                        <fieldset class="fieldset w-full reasons-fieldset">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-green-600">
                                Entry Rules
                            </legend>

                            @php
                                $entryRules = old('entry_rules', $strategy->rules->where('type', 'entry')->pluck('rule')->toArray());
                                if (empty($entryRules)) {
                                    $entryRules = ['']; // fallback to 1 empty row
                                }
                            @endphp

                            @foreach($entryRules as $rule)
                                <div class="flex items-center gap-2 w-full reason-container">
                                    <input type="text" placeholder="e.g. Price must close above 50 EMA"
                                        class="input flex-grow" name="entry_rules[]" value="{{ $rule }}" />
                                    <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                        aria-label="Delete Rule">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach

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

                            @php
                                $exitRules = old('exit_rules', $strategy->rules->where('type', 'exit')->pluck('rule')->toArray());
                                if (empty($exitRules)) {
                                    $exitRules = [''];
                                }
                            @endphp

                            @foreach($exitRules as $rule)
                                <div class="flex items-center gap-2 w-full reason-container">
                                    <input type="text" placeholder="e.g. Stop loss hit or trailing triggered"
                                        class="input flex-grow" name="exit_rules[]" value="{{ $rule }}" />
                                    <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                        aria-label="Delete Rule">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach

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

                            @php
                                $riskRules = old('risk_management_rules', $strategy->rules->where('type', 'risk_management')->pluck('rule')->toArray());
                                if (empty($riskRules)) {
                                    $riskRules = [''];
                                }
                            @endphp

                            @foreach($riskRules as $rule)
                                <div class="flex items-center gap-2 w-full reason-container">
                                    <input type="text" placeholder="e.g. Move SL to breakeven at 1R" class="input flex-grow"
                                        name="risk_management_rules[]" value="{{ $rule }}" />
                                    <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                        aria-label="Delete Rule">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach

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

                            @php
                                $scalingRules = old('scaling_rules', $strategy->rules->where('type', 'scaling')->pluck('rule')->toArray());
                                if (empty($scalingRules)) {
                                    $scalingRules = [''];
                                }
                            @endphp

                            @foreach($scalingRules as $rule)
                                <div class="flex items-center gap-2 w-full reason-container">
                                    <input type="text" placeholder="e.g. Take 50% profit at 2R" class="input flex-grow"
                                        name="scaling_rules[]" value="{{ $rule }}" />
                                    <button type="button" class="btn btn-square btn-error btn-outline delete-btn"
                                        aria-label="Delete Rule">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach

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
                <div>
                    <a href="{{ route('strategies.index') }}">Cancel</a>
                </div>
                <button class="btn btn-primary" type="submit">Save Strategy</button>
            </div>
        </form>
    </div>
</x-layouts.app>

@include('components.dynamic-repeater-script')
@include('components.form-dirty-state-check')