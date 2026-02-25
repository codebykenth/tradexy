<?php

namespace App\Console\Commands;

use App\Mail\Errors\FetchClosedPnlMail;
use App\Models\Trade;
use App\Models\User;
use App\Services\BybitService;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class FetchClosedPnl extends Command
{
    private $user;
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
        $this->user = User::where('email', config('services.bybit.user_email'))->first();
        $this->info('Fetching closed PnL from Bybit...');

        try {
            $bybit = new BybitService();

            $user = $this->user;

            if (!$user) {
                $this->error('Bybit user not found. Set BYBIT_USER_EMAIL in .env');
                return 1;
            }

            $this->info("Fetching for: {$user->name}");

            $response = $bybit->getClosedPnl(days: 2);

            $trades = [];
            $errors = [];

            if (($response['retCode'] ?? -1) === 0 && isset($response['result']['list'])) {
                $trades = $response['result']['list'];
                dump($trades);
            } else {
                $errors[] = [
                    'error' => $response['retMsg'] ?? 'Unknown error',
                ];
            }

            // Sort by updatedTime ascending (oldest first)
            usort($trades, function ($a, $b) {
                $timeA = (int) ($a['updatedTime'] ?? $a['createdTime'] ?? 0);
                $timeB = (int) ($b['updatedTime'] ?? $b['createdTime'] ?? 0);
                return $timeA - $timeB;
            });

            // Save trades to database
            $created = 0;
            $skipped = 0;

            foreach ($trades as $trade) {
                // Bybit's "side" is the CLOSING side, so entry is the opposite
                $closeSide = strtolower($trade['side']) === 'buy' ? 'long' : 'short';
                $entrySide = $closeSide === 'long' ? 'short' : 'long';

                // Convert millisecond timestamps to datetime
                $openDatetime = Carbon::createFromTimestampMs((int) $trade['createdTime']);
                $closeDatetime = Carbon::createFromTimestampMs((int) $trade['updatedTime']);

                // Use firstOrCreate to prevent duplicate trades
                DB::beginTransaction();
                $result = Trade::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'order_id' => $trade['orderId'],
                    ],
                    [
                        'symbol' => $trade['symbol'],
                        'entry_side' => $entrySide,
                        'exit_side' => $closeSide,
                        'entry_price' => $trade['avgEntryPrice'],
                        'exit_price' => $trade['avgExitPrice'],
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
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->error("Error: {$error['error']}");
                }
            }

            $this->info("API returned: " . count($trades) . " trades");
            $this->info("Created: {$created} | Skipped (duplicates): {$skipped}");
            $this->info('Done!');

        } catch (\Exception $e) {
            $this->error("Failed: {$e->getMessage()}");
            Mail::to($this->user->email)->send(new FetchClosedPnlMail($e->getMessage()));
            DB::rollBack();
            return 1;
        }

        return 0;
    }
}

