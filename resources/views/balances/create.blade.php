<x-layouts.app>
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between my-4">
            <div>
                <h1 class="text-2xl font-bold">Log Manual Balance Entry
                </h1>
                <p>Record a snapshot of your account balance and equity.</p>
            </div>
            <div>
                <a href="{{ route('balances.index') }}"><- Back to balances</a>
            </div>
        </div>
        <form action="{{ route('balances.store') }}" method="post" class="bg-gray-100 rounded-lg p-8 my-8" id="form">
            @csrf
            <div class="">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Date & Time
                            </legend>
                            <input type="datetime-local" class="input w-full" name="date"/>
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Wallet Balance</legend>
                            <input type="number" step="any" class="input w-full" placeholder="" name="wallet_balance"/>
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Unrealized PnL (Optional)</legend>
                            <input type="number" step="any" class="input w-full" placeholder=""/>
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Total Equity</legend>
                            <input type="number" step="any" class="input w-full" placeholder="" name="total_equity"/>
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                    <div>
                        <fieldset class="fieldset w-full">
                            <legend
                                class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                Cuumulative Realized PnL</legend>
                            <input type="number" step="any" class="input w-full" placeholder="" name="cum_realised_pnl"/>
                            <!-- Put Error Here -->
                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-8 justify-end">
                <div>
                    <a href="{{ route('balances.index') }}">Cancel</a>
                </div>
                <button class="btn btn-primary" type="submit">Save Entry</button>
            </div>
        </form>
    </div>
</x-layouts.app>

@include('components.form-dirty-state-check')