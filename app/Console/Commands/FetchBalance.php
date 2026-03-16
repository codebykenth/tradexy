<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Balance;
use App\Models\User;
use App\Services\BybitService;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class FetchBalance extends Command
{
    private ?User $user = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:fetch-balance {--demo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch account balance from Bybit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->user = User::where('email', config('services.bybit.user_email'))->first();
        $this->info('Fetching account balance from Bybit...');

        try {
            $isDemo = $this->option('demo');
            $bybitService = new BybitService($isDemo);
            $user = $this->user;

            if (!$user) {
                $this->error('User not found.');

                return 1;
            }

            $this->info("Fetching for: {$user->name}".($isDemo ? ' [DEMO]' : ' [MAIN]'));

            $balanceResponse = $bybitService->getAccountBalance();

            if (isset($balanceResponse['error'])) {
                throw new \Exception($balanceResponse['error']->getMessage());
            }

            $balance = $balanceResponse['result']['list'][0];
            $usdtData = $balance['coin'][0];

            DB::beginTransaction();
            Balance::create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'total_equity' => $usdtData['equity'],
                'wallet_balance' => $usdtData['walletBalance'],
                'cum_realised_pnl' => $usdtData['cumRealisedPnl'],
                'is_demo' => $isDemo,
                'market' => 'crypto',
            ]);
            DB::commit();

            // Log success
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'bybit_balance_sync',
                'description' => 'Synced balance from Bybit ('.($isDemo ? 'Demo' : 'Main')."): {$usdtData['equity']} USDT",
                'ip_address' => gethostbyname(gethostname()),
                'user_agent' => 'Artisan: account:fetch-balance ('.php_uname('n').')',
            ]);

            $this->info('Done!');
        } catch (\Exception $e) {
            $this->error("Failed: {$e->getMessage()}");

            // Log failure
            if ($this->user instanceof User) {
                ActivityLog::create([
                    'user_id' => $this->user->id,
                    'action' => 'bybit_balance_failed',
                    'description' => 'Bybit balance sync failed: '.substr($e->getMessage(), 0, 255),
                    'ip_address' => gethostbyname(gethostname()),
                    'user_agent' => 'Artisan: account:fetch-balance ('.php_uname('n').')',
                ]);
            }

            Mail::to($this->user->email ?? config('mail.from.address'))->send(
                new \App\Mail\Errors\GenericJobFailedMail('Bybit Balance Sync', $e->getMessage())
            );
            DB::rollBack();

            return 1;
        }
    }
}
