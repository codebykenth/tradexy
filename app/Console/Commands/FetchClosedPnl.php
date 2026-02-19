<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BybitService;
use Illuminate\Console\Command;

class FetchClosedPnl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trades:fetch-pnl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch closed PnL from Bybit API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching closed PnL from Bybit...');

        try {
            $bybit = new BybitService();

            $user = User::where('email', config('services.bybit.user_email'))->first();

            if (!$user) {
                $this->error('Bybit user not found. Set BYBIT_USER_EMAIL in .env');
                return 1;
            }

            $this->info("Fetching for: {$user->name}");

            $result = $bybit->getClosedPnl(userId: $user->id, days: 2);

            if (count($result['errors']) > 0) {
                foreach ($result['errors'] as $error) {
                    $this->error("Error: {$error['error']}");
                }
            }

            $this->info("API returned: {$result['summary']['totalFromApi']} trades");
            $this->info("Created: {$result['created']} | Skipped (duplicates): {$result['skipped']}");
            $this->info('Done!');

        } catch (\Exception $e) {
            $this->error("Failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}

