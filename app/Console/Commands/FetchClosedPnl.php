<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Trade;
use App\Models\User;
use App\Services\BybitService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class FetchClosedPnl extends Command
{
    private ?User $user = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trades:fetch-pnl {--demo}';

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
        $this->user = User::where('email', config('services.bybit.user_email'))->first();
        $this->info('Fetching closed PnL from Bybit...');

        try {
            $isDemo = $this->option('demo');
            $bybit = new BybitService($isDemo);

            $user = $this->user;

            if (!$user) {
                $this->error('Bybit user not found. Set BYBIT_USER_EMAIL in .env');

                return 1;
            }

            $this->info("Fetching for: {$user->name}".($isDemo ? ' [DEMO]' : ' [MAIN]'));

            $response = $bybit->getClosedPnl(days: 20);

            $trades = [];
            $errors = [];

            if (($response['retCode'] ?? -1) === 0 && isset($response['result']['list'])) {
                $trades = $response['result']['list'];
            } else {
                $errors[] = [
                    'error' => $response['retMsg'] ?? 'Unknown error',
                ];
            }

            // Sort by updatedTime ascending (oldest first)
            usort($trades, function ($a, $b) {
                $timeA = (int) ($a['updatedTime'] ?? $a['createdTime'] ?? 0);
                $timeB = (int) ($a['updatedTime'] ?? $a['createdTime'] ?? 0);

                return $timeA - $timeB;
            });

            // Save trades to database
            $created = 0;
            $skipped = 0;

            DB::beginTransaction();
            foreach ($trades as $trade) {
                // Bybit's "side" is the CLOSING side, so entry is the opposite
                $closeSide = strtolower($trade['side']) === 'buy' ? 'long' : 'short';
                $entrySide = $closeSide === 'long' ? 'short' : 'long';

                // Convert millisecond timestamps — store as UTC in database
                $openDatetime = Carbon::createFromTimestampMs((int) $trade['createdTime'])->utc();
                $closeDatetime = Carbon::createFromTimestampMs((int) $trade['updatedTime'])->utc();

                // Use firstOrCreate to prevent duplicate trades
                $result = Trade::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'order_id' => $trade['orderId'],
                        'is_demo' => $isDemo,
                    ],
                    [
                        'market' => 'crypto',
                        'symbol' => $trade['symbol'],
                        'entry_side' => $entrySide,
                        'exit_side' => $closeSide,
                        'quantity' => $trade['closedSize'],
                        'cum_entry_value' => $trade['cumEntryValue'],
                        'cum_exit_value' => $trade['cumExitValue'],
                        'avg_entry_price' => $trade['avgEntryPrice'],
                        'avg_exit_price' => $trade['avgExitPrice'],
                        'leverage' => $trade['leverage'],
                        'open_fees' => $trade['openFee'] ?? 0,
                        'close_fees' => $trade['closeFee'] ?? 0,
                        'closed_pnl' => $trade['closedPnl'],
                        'total_pnl' => $trade['closedPnl'],
                        'open_datetime' => $openDatetime,
                        'close_datetime' => $closeDatetime,
                    ]
                );
                $result->wasRecentlyCreated ? $created++ : $skipped++;
            }
            DB::commit();

            // Log successful sync
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'bybit_sync',
                'description' => 'Synced trades from Bybit ('.($isDemo ? 'Demo' : 'Main')."). Created: {$created}, Skipped: {$skipped}",
                'ip_address' => gethostbyname(gethostname()),
                'user_agent' => 'Artisan: trades:fetch-pnl ('.php_uname('n').')',
            ]);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->error("Error: {$error['error']}");
                }
            }

            $this->info('API returned: '.count($trades).' trades');
            $this->info("Created: {$created} | Skipped (duplicates): {$skipped}");
            $this->info('Done!');

            if ($created > 0) {
                $accountType = $isDemo ? 'Demo' : 'Bybit';
                \App\Events\NewTradesFetched::dispatch($this->user, "Added {$created} new trades from {$accountType}!");
            }

        } catch (\Exception $e) {
            $this->error("Failed: {$e->getMessage()}");

            // Log failed sync
            // Log failed sync
            if ($this->user instanceof User) {
                ActivityLog::create([
                    'user_id' => $this->user->id,
                    'action' => 'bybit_sync_failed',
                    'description' => 'Bybit sync failed: '.substr($e->getMessage(), 0, 255),
                    'ip_address' => gethostbyname(gethostname()),
                    'user_agent' => 'Artisan: trades:fetch-pnl ('.php_uname('n').')',
                ]);
            }

            Mail::to($this->user->email ?? config('mail.from.address'))->send(
                new \App\Mail\Errors\GenericJobFailedMail('Bybit Trade Sync', $e->getMessage())
            );
            DB::rollBack();

            return 1;
        }

        return 0;
    }
}
