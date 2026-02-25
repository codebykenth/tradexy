<?php

namespace App\Console\Commands;

use App\Mail\Errors\FetchBalanceMail;
use App\Models\Balance;
use App\Models\User;
use App\Services\BybitService;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class FetchBalance extends Command
{
    private $user;
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
        $this->user = User::where('email', config('services.bybit.user_email'))->first();
        $this->info('Fetching account balance from Bybit...');
        try {
            $bybitService = new BybitService();
            $user = $this->user;

            $this->info("Fetching for: {$user->name}");

            $balance = $bybitService->getAccountBalance()['result']['list'][0];

            $usdtData = $balance['coin'][0];
            dump($usdtData);

            DB::beginTransaction();
            Balance::create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'total_equity' => $usdtData['equity'],
                'wallet_balance' => $usdtData['walletBalance'],
                'cum_realised_pnl' => $usdtData['cumRealisedPnl'],
            ]);
            DB::commit();
            $this->info('Done!');
        } catch (\Exception $e) {
            $this->error("Failed: {$e->getMessage()}");
            Mail::to($this->user->email)->send(new FetchBalanceMail($e->getMessage()));
            DB::rollBack();
            return 1;
        }
    }
}
