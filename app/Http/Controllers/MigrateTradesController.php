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

        $response = Http::withToken($apiToken)->get("$apiUrl/trade-logs");

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

            if ($result->wasRecentlyCreated) {
                // Migrate Entry Reasons
                if (!empty($old['entry_reason'])) {
                    $entryReasons = is_array($old['entry_reason']) ? $old['entry_reason'] : json_decode($old['entry_reason'], true);
                    if (is_array($entryReasons)) {
                        foreach ($entryReasons as $reason) {
                            if (empty($reason))
                                continue;
                            \App\Models\Reason::create([
                                'trade_id' => $result->id,
                                'type' => 'entry',
                                'reason' => $reason
                            ]);
                        }
                    }
                }

                // Migrate Exit Reasons
                if (!empty($old['exit_reason'])) {
                    $exitReasons = is_array($old['exit_reason']) ? $old['exit_reason'] : json_decode($old['exit_reason'], true);
                    if (is_array($exitReasons)) {
                        foreach ($exitReasons as $reason) {
                            if (empty($reason))
                                continue;
                            \App\Models\Reason::create([
                                'trade_id' => $result->id,
                                'type' => 'exit',
                                'reason' => $reason
                            ]);
                        }
                    }
                }

                // Migrate Lessons
                if (!empty($old['lessons_learned'])) {
                    $lessons = is_array($old['lessons_learned']) ? $old['lessons_learned'] : json_decode($old['lessons_learned'], true);
                    if (is_array($lessons)) {
                        foreach ($lessons as $lesson) {
                            if (empty($lesson))
                                continue;
                            \App\Models\Lesson::create([
                                'trade_id' => $result->id,
                                'lesson' => $lesson,
                                'category' => 'migration' // Defaulting to migration category since old API doesn't specify
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Migration complete!',
            'total_from_api' => count($oldTrades),
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }
}
