<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class MigrateTradesController extends Controller
{
    public function migrate()
    {
        $apiUrl = config('services.old_journal.url');
        $apiToken = config('services.old_journal.token');

        if (!$apiUrl || !$apiToken) {
            return response()->json(['error' => 'OLD_JOURNAL_API_URL and OLD_JOURNAL_API_TOKEN not set in .env'], 500);
        }

        $user = User::where('email', config('services.bybit.user_email'))->first();

        if (!$user) {
            return response()->json(['error' => 'User not found. Set BYBIT_USER_EMAIL in .env'], 404);
        }

        $response = Http::withToken($apiToken)->get($apiUrl);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'API request failed',
                'status' => $response->status(),
                'body' => $response->body(),
            ], 500);
        }

        $oldTrades = $response->json();

        if (empty($oldTrades)) {
            return response()->json(['message' => 'No trades found in old journal.']);
        }
        
        usort($oldTrades, function ($a, $b) {
            return strtotime($a['open_datetime']) - strtotime($b['open_datetime']);
        });

        $created = 0;
        $skipped = 0;

        foreach ($oldTrades as $old) {
            $result = Trade::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'order_id' => $old['order_id'],
                ],
                [
                    'symbol' => $old['symbol'],
                    'entry_side' => strtolower($old['entry_side']),
                    'exit_side' => strtolower($old['exit_side']),
                    'entry_price' => $old['avg_entry_price'],
                    'exit_price' => $old['avg_exit_price'],
                    'quantity' => $old['qty'] ?? $old['closed_size'],
                    'cum_entry_value' => $old['cum_entry_value'],
                    'cum_exit_value' => $old['cum_exit_value'],
                    'avg_entry_price' => $old['avg_entry_price'],
                    'avg_exit_price' => $old['avg_exit_price'],
                    'leverage' => $old['leverage'],
                    'open_fees' => $old['open_fee'] ?? 0,
                    'close_fees' => $old['close_fee'] ?? 0,
                    'closed_pnl' => $old['closed_pnl'],
                    'total_pnl' => $old['total_pnl'],
                    'open_datetime' => $old['open_datetime'],
                    'close_datetime' => $old['close_datetime'],
                    'chart_picture' => $old['chart_url'],
                    'timeframe' => $old['timeframe'],
                    'take_profit_price' => $old['takeprofit'],
                    'stop_loss_price' => $old['stoploss'],
                    'entry_emotion' => $old['emotion_entry'],
                    'exit_emotion' => $old['emotion_exit'],
                    'ai_analysis' => $old['ai_analysis'],
                ]
            );

            $result->wasRecentlyCreated ? $created++ : $skipped++;
        }

        return response()->json([
            'message' => 'Migration complete!',
            'total_from_api' => count($oldTrades),
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }
}
