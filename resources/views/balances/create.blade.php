<x-layouts.app title="Add Balance Entry - Tradexy">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between my-4">
            <div>
                <x-page-title title="Log Manual Balance Entry"
                    subtitle="Record a snapshot of your account balance and equity." />
            </div>
        </div>
        <form action="{{ route('balances.store') }}" method="post" class="bg-base-200 rounded-lg p-8 my-8" id="form">
            @csrf
            <x-errors />
            <div class="">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/70">
                                Date & Time
                            </legend>
                            <input type="datetime-local" class="input w-full" name="date" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/70">
                                Wallet Balance</legend>
                            <input type="number" step="any" class="input w-full" placeholder="" name="wallet_balance" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/70">
                                Unrealized PnL (Optional)</legend>
                            <input type="number" step="any" class="input w-full" placeholder="" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/70">
                                Total Equity</legend>
                            <input type="number" step="any" class="input w-full" placeholder="" name="total_equity" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/70">
                                Cuumulative Realized PnL</legend>
                            <input type="number" step="any" class="input w-full" placeholder=""
                                name="cum_realised_pnl" />
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/70">
                                Market</legend>
                            <select class="select w-full" name="market">
                                <option value="crypto" @selected(old('market', session('market_type') === 'all' ? 'crypto' : session('market_type', 'crypto')) === 'crypto')>Crypto</option>
                                <option value="pse" @selected(old('market', session('market_type') === 'all' ? 'crypto' : session('market_type', 'crypto')) === 'pse')>PSE</option>
                            </select>
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-base-content/70">
                                Account Mode</legend>
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="is_demo" value="1" class="checkbox checkbox-warning" {{ old('is_demo', session('account_mode') === 'demo') ? 'checked' : '' }} />
                                <span class="label-text font-bold text-warning uppercase">Demo Entry</span>
                            </label>
                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-8 justify-end">
                <button class="btn btn-primary" type="submit">Save Entry</button>
            </div>
        </form>
    </div>
    @include('components.form-dirty-state-check')
</x-layouts.app>