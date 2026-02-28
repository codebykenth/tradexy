<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Reason;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

final class MigrateTradesController extends Controller
{
    // Migrates trades from old journal API using bulk inserts to avoid timeouts
    public function migrate(): JsonResponse
    {
        set_time_limit(0);

        $apiUrl = config('services.old_journal.url');
        $apiToken = config('services.old_journal.token');

        if (!$apiUrl || !$apiToken) {
            return response()->json(['error' => 'Migration API configuration is missing.'], 500);
        }

        $user = User::where('email', config('services.bybit.user_email'))->first();

        if (!$user) {
            return response()->json(['error' => 'Migration user not found.'], 404);
        }

        $response = Http::timeout(300)->withToken($apiToken)->get("$apiUrl/trade-logs");

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

        usort($oldTrades, fn($a, $b) => strtotime($a['open_datetime']) - strtotime($b['open_datetime']));

        $total = count($oldTrades);
        $created = 0;
        $skipped = 0;

        // Pre-fetch existing order_ids to skip duplicates without per-row DB lookups
        $existingOrderIds = Trade::where('user_id', $user->id)
            ->pluck('order_id')
            ->flip()
            ->toArray();

        foreach (array_chunk($oldTrades, 25) as $chunk) {
            $tradesToInsert = [];
            $newTradeOrderIds = [];

            foreach ($chunk as $old) {
                $orderId = $old['order_id'];

                if (isset($existingOrderIds[$orderId])) {
                    $skipped++;
                    continue;
                }

                $now = now();
                $tradesToInsert[] = [
                    'user_id' => $user->id,
                    'order_id' => $orderId,
                    'strategy_id' => $old['strategy_id'],
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
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $newTradeOrderIds[$orderId] = $old;
                $existingOrderIds[$orderId] = true;
            }

            if (empty($tradesToInsert)) {
                continue;
            }

            DB::transaction(function () use ($user, $tradesToInsert, $newTradeOrderIds, &$created): void {
                Trade::insert($tradesToInsert);
                $created += count($tradesToInsert);

                // Fetch newly created trade IDs by order_id for relation inserts
                $newTrades = Trade::where('user_id', $user->id)
                    ->whereIn('order_id', array_keys($newTradeOrderIds))
                    ->pluck('id', 'order_id');

                $reasonsToInsert = [];
                $lessonsToInsert = [];

                foreach ($newTradeOrderIds as $orderId => $old) {
                    $tradeId = $newTrades[$orderId] ?? null;
                    if (!$tradeId) {
                        continue;
                    }

                    $now = now();
                    $this->collectReasons($reasonsToInsert, $old['entry_reason'] ?? null, $tradeId, 'entry', $now);
                    $this->collectReasons($reasonsToInsert, $old['exit_reason'] ?? null, $tradeId, 'exit', $now);
                    $this->collectLessons($lessonsToInsert, $old['lessons_learned'] ?? null, $tradeId, $now);
                }

                foreach (array_chunk($reasonsToInsert, 100) as $batch) {
                    Reason::insert($batch);
                }

                foreach (array_chunk($lessonsToInsert, 100) as $batch) {
                    Lesson::insert($batch);
                }
            });
        }

        return response()->json([
            'message' => 'Migration complete!',
            'total_from_api' => $total,
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    // Parses and collects reason records for bulk insert
    private function collectReasons(array &$bucket, mixed $raw, int $tradeId, string $type, mixed $now): void
    {
        if (empty($raw)) {
            return;
        }

        $items = is_array($raw) ? $raw : json_decode($raw, true);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $reason) {
            if (empty($reason)) {
                continue;
            }
            $bucket[] = [
                'trade_id' => $tradeId,
                'type' => $type,
                'reason' => $reason,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    // Parses and collects lesson records for bulk insert
    private function collectLessons(array &$bucket, mixed $raw, int $tradeId, mixed $now): void
    {
        if (empty($raw)) {
            return;
        }

        $items = is_array($raw) ? $raw : json_decode($raw, true);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $lesson) {
            if (empty($lesson)) {
                continue;
            }
            $bucket[] = [
                'trade_id' => $tradeId,
                'lesson' => $lesson,
                'category' => 'migration',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }
}
