<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\Lesson;
use App\Models\Reason;
use App\Models\Strategy;
use App\Models\StrategyRules;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Seeds mock trading data for development and testing.
final class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Create the seed user
        $user = User::firstOrCreate(
            ['email' => 'demo@tradexy.app'],
            [
                'name' => 'Demo Trader',
                'password' => Hash::make('password'),
                'account_mode' => 'demo',
                'market_type' => 'crypto',
                'preferred_currency' => 'USD',
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'is_admin' => true,
            ]
        );

        // --- Strategies ---
        $strategyMap = [];

        $strategies = [
            ['name' => 'Trend Following', 'color' => '#3b82f6', 'target_rr' => '2.00', 'max_risk' => '1.00', 'key' => 'trend'],
            ['name' => 'Mean Reversion', 'color' => '#a855f7', 'target_rr' => '1.50', 'max_risk' => '1.50', 'key' => 'mean_rev'],
            ['name' => 'Support Bounce', 'color' => '#22c55e', 'target_rr' => '2.00', 'max_risk' => '1.00', 'key' => 'support'],
            ['name' => 'Resistance Short', 'color' => '#ef4444', 'target_rr' => '2.00', 'max_risk' => '1.00', 'key' => 'resistance'],
            ['name' => 'Breakout Long', 'color' => '#06b6d4', 'target_rr' => '3.00', 'max_risk' => '2.00', 'key' => 'breakout'],
            ['name' => 'Bull Flag Continuation', 'color' => '#f59e0b', 'target_rr' => '2.50', 'max_risk' => '1.50', 'key' => 'bullflag'],
            ['name' => 'Bear Flag Continuation', 'color' => '#dc2626', 'target_rr' => '2.50', 'max_risk' => '1.50', 'key' => 'bearflag'],
            ['name' => 'Range Trading', 'color' => '#8b5cf6', 'target_rr' => '1.00', 'max_risk' => '1.00', 'key' => 'range'],
            ['name' => 'Fibonacci Retracement', 'color' => '#14b8a6', 'target_rr' => '2.00', 'max_risk' => '1.00', 'key' => 'fib'],
            ['name' => 'Momentum Scalp', 'color' => '#f97316', 'target_rr' => '1.50', 'max_risk' => '2.00', 'key' => 'momentum'],
        ];

        foreach ($strategies as $s) {
            $strategy = Strategy::create([
                'user_id' => $user->id,
                'name' => $s['name'],
                'description' => null,
                'category' => null,
                'status' => 'active',
                'color' => $s['color'],
                'target_rr' => $s['target_rr'],
                'max_risk_per_trade' => $s['max_risk'],
                'timeframes' => null,
                'markets' => null,
            ]);
            $strategyMap[$s['key']] = $strategy->id;
        }

        // Add rules to Bear Flag strategy
        $bearFlagId = $strategyMap['bearflag'];
        $rules = [
            ['type' => 'entry', 'rule' => 'Confirm strong preceding downtrend on higher timeframe', 'order' => 0],
            ['type' => 'entry', 'rule' => 'Wait for consolidation forming a flag pattern', 'order' => 1],
            ['type' => 'entry', 'rule' => 'Enter on breakdown below flag support with volume', 'order' => 2],
            ['type' => 'exit', 'rule' => 'Target measured move equal to flagpole length', 'order' => 3],
            ['type' => 'exit', 'rule' => 'Trail stop above last swing high within the flag', 'order' => 4],
            ['type' => 'risk_management', 'rule' => 'Stop loss above flag high — max 2% account risk', 'order' => 5],
            ['type' => 'risk_management', 'rule' => 'Move stop to breakeven after 1R profit', 'order' => 6],
            ['type' => 'scaling', 'rule' => 'Add 50% position on retest of broken flag support', 'order' => 7],
        ];
        foreach ($rules as $r) {
            StrategyRules::create(array_merge($r, ['strategy_id' => $bearFlagId]));
        }

        // Add rules to Breakout Long strategy
        $breakoutId = $strategyMap['breakout'];
        $breakoutRules = [
            ['type' => 'entry', 'rule' => 'Price must close above resistance on higher timeframe', 'order' => 0],
            ['type' => 'entry', 'rule' => 'Volume must be 1.5x average on breakout candle', 'order' => 1],
            ['type' => 'exit', 'rule' => 'Take profit at next major resistance level', 'order' => 2],
            ['type' => 'risk_management', 'rule' => 'Stop loss below breakout candle low', 'order' => 3],
        ];
        foreach ($breakoutRules as $r) {
            StrategyRules::create(array_merge($r, ['strategy_id' => $breakoutId]));
        }

        // --- Demo Trades ---
        // Trade 1: BTCUSDT Long — Win
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['support'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'BTCUSDT',
            'entry_side' => 'long',
            'exit_side' => 'short',
            'quantity' => '0.04200000',
            'cum_entry_value' => '3528.00000000',
            'cum_exit_value' => '3570.00000000',
            'avg_entry_price' => '84000.00000000',
            'avg_exit_price' => '85000.00000000',
            'entry_emotion' => 'Confident',
            'exit_emotion' => 'Confident',
            'take_profit_price' => '85200.00000000',
            'stop_loss_price' => '83500.00000000',
            'timeframe' => '15m',
            'leverage' => '10.00',
            'chart_picture' => null,
            'ai_analysis' => 'Strong confluence of key horizontal support with 15m bullish divergence. Risk-to-reward is well-structured at 2.4R.',
            'open_fees' => '0.70560000',
            'close_fees' => '1.96350000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '42.00000000',
            'total_pnl' => '39.33090000',
            'open_datetime' => '2026-03-10 08:15:00',
            'close_datetime' => '2026-03-10 10:45:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Strong support bounce at 84K', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'RSI oversold on 15m', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Take profit hit at resistance', 'is_primary' => false]);
        Lesson::create(['trade_id' => $trade->id, 'lesson' => 'Patience on support levels pays off — waited for confirmation candle', 'category' => 'N/A', 'is_positive' => true]);

        // Trade 2: ETHUSDT Short — Loss
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['resistance'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'ETHUSDT',
            'entry_side' => 'short',
            'exit_side' => 'long',
            'quantity' => '0.50000000',
            'cum_entry_value' => '1750.00000000',
            'cum_exit_value' => '1785.00000000',
            'avg_entry_price' => '3500.00000000',
            'avg_exit_price' => '3570.00000000',
            'entry_emotion' => 'FOMO',
            'exit_emotion' => 'Anxious',
            'take_profit_price' => '3400.00000000',
            'stop_loss_price' => '3575.00000000',
            'timeframe' => '5m',
            'leverage' => '10.00',
            'chart_picture' => null,
            'open_fees' => '0.35000000',
            'close_fees' => '0.98175000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '-35.00000000',
            'total_pnl' => '-36.33175000',
            'open_datetime' => '2026-03-11 14:30:00',
            'close_datetime' => '2026-03-11 15:10:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Resistance rejection candle spotted', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Entered too early before confirmation', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Stop loss hit — broke above resistance', 'is_primary' => false]);
        Lesson::create(['trade_id' => $trade->id, 'lesson' => 'Should not enter on FOMO — wait for candle close confirmation', 'category' => 'N/A', 'is_positive' => false]);

        // Trade 3: BTCUSDT Short — Win
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['momentum'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'BTCUSDT',
            'entry_side' => 'short',
            'exit_side' => 'long',
            'quantity' => '0.02800000',
            'cum_entry_value' => '2380.00000000',
            'cum_exit_value' => '2352.00000000',
            'avg_entry_price' => '85000.00000000',
            'avg_exit_price' => '84000.00000000',
            'entry_emotion' => 'Confident',
            'exit_emotion' => 'Confident',
            'take_profit_price' => '83800.00000000',
            'stop_loss_price' => '85400.00000000',
            'timeframe' => '1m',
            'leverage' => '10.00',
            'chart_picture' => null,
            'open_fees' => '0.47600000',
            'close_fees' => '1.29360000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '28.00000000',
            'total_pnl' => '26.23040000',
            'open_datetime' => '2026-03-12 09:00:00',
            'close_datetime' => '2026-03-12 09:25:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Strong bearish momentum — consecutive red candles', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Lower highs forming on 1m chart', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Price approaching key support zone', 'is_primary' => false]);

        // Trade 4: SOLUSDT Long — Win
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['breakout'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'SOLUSDT',
            'entry_side' => 'long',
            'exit_side' => 'short',
            'quantity' => '10.00000000',
            'cum_entry_value' => '1500.00000000',
            'cum_exit_value' => '1560.00000000',
            'avg_entry_price' => '150.00000000',
            'avg_exit_price' => '156.00000000',
            'entry_emotion' => 'Confident',
            'exit_emotion' => 'Excited',
            'take_profit_price' => '158.00000000',
            'stop_loss_price' => '148.00000000',
            'timeframe' => '15m',
            'leverage' => '10.00',
            'chart_picture' => null,
            'ai_analysis' => 'Clean consolidation pattern breakout with significant volume surge. Target and stop-loss levels aligned with strategy guidelines.',
            'open_fees' => '0.30000000',
            'close_fees' => '0.85800000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '60.00000000',
            'total_pnl' => '58.84200000',
            'open_datetime' => '2026-03-13 06:00:00',
            'close_datetime' => '2026-03-13 08:30:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Breakout above consolidation range with volume', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Reached 3R target — closed at resistance', 'is_primary' => false]);
        Lesson::create(['trade_id' => $trade->id, 'lesson' => 'Breakout trades work best with volume confirmation', 'category' => 'N/A', 'is_positive' => true]);

        // Trade 5: BTCUSDT Long — Loss
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['trend'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'BTCUSDT',
            'entry_side' => 'long',
            'exit_side' => 'short',
            'quantity' => '0.04000000',
            'cum_entry_value' => '3360.00000000',
            'cum_exit_value' => '3296.00000000',
            'avg_entry_price' => '84000.00000000',
            'avg_exit_price' => '82400.00000000',
            'entry_emotion' => 'Neutral',
            'exit_emotion' => 'Fearful',
            'take_profit_price' => '86000.00000000',
            'stop_loss_price' => '82300.00000000',
            'timeframe' => '1hr',
            'leverage' => '10.00',
            'chart_picture' => null,
            'open_fees' => '0.67200000',
            'close_fees' => '1.81280000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '-64.00000000',
            'total_pnl' => '-66.48480000',
            'open_datetime' => '2026-03-14 02:00:00',
            'close_datetime' => '2026-03-14 06:30:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Assumed uptrend continuation', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Stop loss hit — trend reversal', 'is_primary' => false]);
        Lesson::create(['trade_id' => $trade->id, 'lesson' => 'Always check for divergence on higher timeframe before entries', 'category' => 'N/A', 'is_positive' => false]);

        // Trade 6: NEARUSDT Short — Loss
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['range'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'NEARUSDT',
            'entry_side' => 'short',
            'exit_side' => 'long',
            'quantity' => '1500.00000000',
            'cum_entry_value' => '2250.00000000',
            'cum_exit_value' => '2310.00000000',
            'avg_entry_price' => '1.50000000',
            'avg_exit_price' => '1.54000000',
            'entry_emotion' => 'Hesitant',
            'exit_emotion' => 'Neutral',
            'take_profit_price' => '1.42000000',
            'stop_loss_price' => '1.54500000',
            'timeframe' => '1hr',
            'leverage' => '10.00',
            'chart_picture' => null,
            'open_fees' => '0.45000000',
            'close_fees' => '1.27050000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '-60.00000000',
            'total_pnl' => '-61.72050000',
            'open_datetime' => '2026-03-15 01:30:00',
            'close_datetime' => '2026-03-15 03:00:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Expected rejection at range resistance', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Broke above and held — counter-trend entry invalidated', 'is_primary' => false]);
        Lesson::create(['trade_id' => $trade->id, 'lesson' => 'Avoid counter-trend trades in strong momentum environments', 'category' => 'N/A', 'is_positive' => false]);

        // Trade 7: ETHUSDT Long — Win
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['fib'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'ETHUSDT',
            'entry_side' => 'long',
            'exit_side' => 'short',
            'quantity' => '0.60000000',
            'cum_entry_value' => '2100.00000000',
            'cum_exit_value' => '2160.00000000',
            'avg_entry_price' => '3500.00000000',
            'avg_exit_price' => '3600.00000000',
            'entry_emotion' => 'Confident',
            'exit_emotion' => 'Confident',
            'take_profit_price' => '3620.00000000',
            'stop_loss_price' => '3450.00000000',
            'timeframe' => '15m',
            'leverage' => '10.00',
            'chart_picture' => null,
            'open_fees' => '0.42000000',
            'close_fees' => '1.18800000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '60.00000000',
            'total_pnl' => '58.39200000',
            'open_datetime' => '2026-03-16 10:00:00',
            'close_datetime' => '2026-03-16 12:30:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Bounce at Fib 0.618 retracement level', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Reached Fib extension 1.0 target', 'is_primary' => false]);
        Lesson::create(['trade_id' => $trade->id, 'lesson' => 'Fib levels are more reliable when they confluence with horizontal S/R', 'category' => 'N/A', 'is_positive' => true]);

        // Trade 8: BTCUSDT Long — Small Loss
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['bullflag'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'BTCUSDT',
            'entry_side' => 'long',
            'exit_side' => 'short',
            'quantity' => '0.03000000',
            'cum_entry_value' => '2550.00000000',
            'cum_exit_value' => '2535.00000000',
            'avg_entry_price' => '85000.00000000',
            'avg_exit_price' => '84500.00000000',
            'entry_emotion' => 'Confident',
            'exit_emotion' => 'Neutral',
            'take_profit_price' => '86500.00000000',
            'stop_loss_price' => '84400.00000000',
            'timeframe' => '5m',
            'leverage' => '10.00',
            'chart_picture' => null,
            'open_fees' => '0.51000000',
            'close_fees' => '1.39425000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '-15.00000000',
            'total_pnl' => '-16.90425000',
            'open_datetime' => '2026-03-17 05:00:00',
            'close_datetime' => '2026-03-17 06:15:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Bull flag pattern identified', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Flag failed — price broke below flag support', 'is_primary' => false]);

        // Trade 9: SOLUSDT Short — Win
        $trade = Trade::create([
            'user_id' => $user->id,
            'strategy_id' => $strategyMap['bearflag'],
            'order_id' => Str::uuid()->toString(),
            'market' => 'crypto',
            'symbol' => 'SOLUSDT',
            'entry_side' => 'short',
            'exit_side' => 'long',
            'quantity' => '15.00000000',
            'cum_entry_value' => '2250.00000000',
            'cum_exit_value' => '2175.00000000',
            'avg_entry_price' => '150.00000000',
            'avg_exit_price' => '145.00000000',
            'entry_emotion' => 'Confident',
            'exit_emotion' => 'Excited',
            'take_profit_price' => '144.00000000',
            'stop_loss_price' => '152.00000000',
            'timeframe' => '5m',
            'leverage' => '10.00',
            'chart_picture' => null,
            'open_fees' => '0.45000000',
            'close_fees' => '1.19625000',
            'broker_commission' => null,
            'pse_trans_fee' => null,
            'sccp_fee' => null,
            'pse_vat' => null,
            'sales_tax' => null,
            'closed_pnl' => '75.00000000',
            'total_pnl' => '73.35375000',
            'open_datetime' => '2026-03-18 11:00:00',
            'close_datetime' => '2026-03-18 12:45:00',
            'is_demo' => true,
        ]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Bear flag breakdown with strong volume', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'entry', 'reason' => 'Downtrend confirmed on higher timeframe', 'is_primary' => false]);
        Reason::create(['trade_id' => $trade->id, 'type' => 'exit', 'reason' => 'Measured move target reached', 'is_primary' => false]);
        Lesson::create(['trade_id' => $trade->id, 'lesson' => 'Bear flags are high probability when flagpole is strong and clean', 'category' => 'N/A', 'is_positive' => true]);

        // --- Balances (mock equity curve) ---
        $balanceDates = [];
        $startDate = now()->subDays(30);

        // Generate 30 days of demo balance history with a realistic equity curve
        $demoEquity = 50000.00;
        $dailyChanges = [
            0, -120.50, 250.30, -80.00, 180.60, -310.20, 420.80, -50.30, 90.10, -200.70,
            350.40, 120.90, -180.50, 60.20, -25.80, 400.00, -150.30, 210.60, 80.40, -90.10,
            300.50, -40.20, 170.80, -260.30, 110.90, 50.40, -130.70, 220.10, 340.60, -80.20,
        ];

        foreach ($dailyChanges as $i => $change) {
            $demoEquity += $change;
            $date = $startDate->copy()->addDays($i)->format('Y-m-d 00:00:00');

            Balance::create([
                'user_id' => $user->id,
                'date' => $date,
                'total_equity' => number_format($demoEquity, 8, '.', ''),
                'wallet_balance' => number_format($demoEquity, 8, '.', ''),
                'cum_realised_pnl' => number_format($demoEquity - 50000, 8, '.', ''),
                'is_demo' => true,
                'market' => 'crypto',
            ]);
        }
    }
}
