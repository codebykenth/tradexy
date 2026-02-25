<?php

namespace App\Console\Commands;

use App\Models\Balance;
use App\Models\User;
use App\Services\BybitService;
use Illuminate\Console\Command;

class FetchBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:fetch-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $bybitService = new BybitService();
        $user = User::where('email', config('services.bybit.user_email'))->first();

        $balance = $bybitService->getAccountBalance()['result']['list'][0];

        $usdtData = $balance['coin'][0];

        Balance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'total_equity' => $usdtData['equity'],
            'wallet_balance' => $usdtData['totalWalletBalance'],
            'cum_realised_pnl' => $usdtData['cumRealisedPnl'],
        ]);
    }
}
