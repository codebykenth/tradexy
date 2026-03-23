<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Balance;
use App\Models\Strategy;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Console\Command;

// Exports live DB data into a ProductionSeeder file (demo trades only).
final class ExportDatabaseSeeder extends Command
{
    protected $signature = 'db:export-seeder {--user= : User ID to export (defaults to 1)}';

    protected $description = 'Export current database data into a seeder file. Trades are filtered to demo-only.';

    public function handle(): int
    {
        $userId = (int) ($this->option('user') ?? 1);
        $this->info("Exporting data for user ID: {$userId}");

        $user = User::findOrFail($userId);
        $strategies = Strategy::where('user_id', $userId)->with('rules')->get();
        $trades = Trade::where('user_id', $userId)->where('is_demo', true)->with(['reasons', 'lessons'])->get();
        $balances = Balance::where('user_id', $userId)->get();

        $this->info("Found: {$strategies->count()} strategies, {$trades->count()} demo trades, {$balances->count()} balances");

        $output = $this->buildSeederFile($user, $strategies, $trades, $balances);

        $path = database_path('seeders/ProductionSeeder.php');
        file_put_contents($path, $output);

        $this->info("Seeder written to: {$path}");
        $this->info('Run it with: php artisan db:seed --class=ProductionSeeder');

        return Command::SUCCESS;
    }

    // Build the entire seeder PHP file as a string.
    private function buildSeederFile(User $user, $strategies, $trades, $balances): string
    {
        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace Database\Seeders;';
        $lines[] = '';
        $lines[] = 'use App\Models\Balance;';
        $lines[] = 'use App\Models\Lesson;';
        $lines[] = 'use App\Models\Reason;';
        $lines[] = 'use App\Models\Strategy;';
        $lines[] = 'use App\Models\StrategyRules;';
        $lines[] = 'use App\Models\Trade;';
        $lines[] = 'use App\Models\User;';
        $lines[] = 'use Illuminate\Database\Seeder;';
        $lines[] = 'use Illuminate\Support\Facades\Hash;';
        $lines[] = '';
        $lines[] = '// Auto-generated from live DB data. Trades are demo-only.';
        $lines[] = 'final class ProductionSeeder extends Seeder';
        $lines[] = '{';
        $lines[] = '    public function run(): void';
        $lines[] = '    {';

        // User (sanitized — no real password)
        $lines[] = '        // Create the seed user';
        $lines[] = '        $user = User::firstOrCreate(';
        $lines[] = "            ['email' => ".$this->export($user->email).'],';
        $lines[] = '            [';
        $lines[] = "                'name' => ".$this->export($user->name).',';
        $lines[] = "                'password' => Hash::make('password'),";
        $lines[] = "                'account_mode' => ".$this->export($user->account_mode).',';
        $lines[] = "                'market_type' => ".$this->export($user->market_type).',';
        $lines[] = "                'preferred_currency' => ".$this->export($user->preferred_currency).',';
        $lines[] = '            ]';
        $lines[] = '        );';
        $lines[] = '';

        // Strategies
        $lines[] = '        // --- Strategies ---';
        $lines[] = '        $strategyMap = [];';
        foreach ($strategies as $strategy) {
            $lines[] = '        $strategy = Strategy::create([';
            $lines[] = "            'user_id' => \$user->id,";
            $lines[] = "            'name' => ".$this->export($strategy->name).',';
            $lines[] = "            'description' => ".$this->export($strategy->description).',';
            $lines[] = "            'category' => ".$this->export($strategy->getRawOriginal('category')).',';
            $lines[] = "            'status' => ".$this->export($strategy->status).',';
            $lines[] = "            'color' => ".$this->export($strategy->color).',';
            $lines[] = "            'target_rr' => ".$this->export($strategy->target_rr).',';
            $lines[] = "            'max_risk_per_trade' => ".$this->export($strategy->max_risk_per_trade).',';
            $lines[] = "            'timeframes' => ".$this->export($strategy->getRawOriginal('timeframes')).',';
            $lines[] = "            'markets' => ".$this->export($strategy->getRawOriginal('markets')).',';
            $lines[] = '        ]);';
            $lines[] = "        \$strategyMap[{$strategy->id}] = \$strategy->id;";

            // Strategy Rules
            foreach ($strategy->rules as $rule) {
                $lines[] = '        StrategyRules::create([';
                $lines[] = "            'strategy_id' => \$strategy->id,";
                $lines[] = "            'type' => ".$this->export($rule->type).',';
                $lines[] = "            'rule' => ".$this->export($rule->rule).',';
                $lines[] = "            'order' => ".$this->export($rule->order).',';
                $lines[] = '        ]);';
            }
            $lines[] = '';
        }

        // Demo Trades
        $lines[] = '        // --- Demo Trades (only) ---';
        foreach ($trades as $trade) {
            $lines[] = '        $trade = Trade::create([';
            $lines[] = "            'user_id' => \$user->id,";

            // Map strategy_id to the new seeded one
            if ($trade->strategy_id) {
                $lines[] = "            'strategy_id' => \$strategyMap[{$trade->strategy_id}] ?? null,";
            } else {
                $lines[] = "            'strategy_id' => null,";
            }

            $lines[] = "            'order_id' => ".$this->export($trade->order_id).',';
            $lines[] = "            'market' => ".$this->export($trade->market).',';
            $lines[] = "            'symbol' => ".$this->export($trade->symbol).',';
            $lines[] = "            'entry_side' => ".$this->export($trade->entry_side).',';
            $lines[] = "            'exit_side' => ".$this->export($trade->exit_side).',';
            $lines[] = "            'quantity' => ".$this->export($trade->quantity).',';
            $lines[] = "            'cum_entry_value' => ".$this->export($trade->cum_entry_value).',';
            $lines[] = "            'cum_exit_value' => ".$this->export($trade->cum_exit_value).',';
            $lines[] = "            'avg_entry_price' => ".$this->export($trade->avg_entry_price).',';
            $lines[] = "            'avg_exit_price' => ".$this->export($trade->avg_exit_price).',';
            $lines[] = "            'entry_emotion' => ".$this->export($trade->entry_emotion).',';
            $lines[] = "            'exit_emotion' => ".$this->export($trade->exit_emotion).',';
            $lines[] = "            'take_profit_price' => ".$this->export($trade->take_profit_price).',';
            $lines[] = "            'stop_loss_price' => ".$this->export($trade->stop_loss_price).',';
            $lines[] = "            'timeframe' => ".$this->export($trade->timeframe).',';
            $lines[] = "            'leverage' => ".$this->export($trade->leverage).',';
            $lines[] = "            'chart_picture' => null,";
            $lines[] = "            'open_fees' => ".$this->export($trade->open_fees).',';
            $lines[] = "            'close_fees' => ".$this->export($trade->close_fees).',';
            $lines[] = "            'broker_commission' => ".$this->export($trade->broker_commission).',';
            $lines[] = "            'pse_trans_fee' => ".$this->export($trade->pse_trans_fee).',';
            $lines[] = "            'sccp_fee' => ".$this->export($trade->sccp_fee).',';
            $lines[] = "            'pse_vat' => ".$this->export($trade->pse_vat).',';
            $lines[] = "            'sales_tax' => ".$this->export($trade->sales_tax).',';
            $lines[] = "            'closed_pnl' => ".$this->export($trade->closed_pnl).',';
            $lines[] = "            'total_pnl' => ".$this->export($trade->total_pnl).',';
            $lines[] = "            'open_datetime' => ".$this->export($trade->getRawOriginal('open_datetime')).',';
            $lines[] = "            'close_datetime' => ".$this->export($trade->getRawOriginal('close_datetime')).',';
            $lines[] = "            'is_demo' => true,";
            $lines[] = '        ]);';

            // Reasons
            foreach ($trade->reasons as $reason) {
                $lines[] = '        Reason::create([';
                $lines[] = "            'trade_id' => \$trade->id,";
                $lines[] = "            'type' => ".$this->export($reason->type).',';
                $lines[] = "            'reason' => ".$this->export($reason->reason).',';
                $lines[] = "            'is_primary' => ".$this->export($reason->is_primary).',';
                $lines[] = '        ]);';
            }

            // Lessons
            foreach ($trade->lessons as $lesson) {
                $lines[] = '        Lesson::create([';
                $lines[] = "            'trade_id' => \$trade->id,";
                $lines[] = "            'lesson' => ".$this->export($lesson->lesson).',';
                $lines[] = "            'category' => ".$this->export($lesson->category).',';
                $lines[] = "            'is_positive' => ".$this->export($lesson->is_positive).',';
                $lines[] = '        ]);';
            }
            $lines[] = '';
        }

        // Balances
        $lines[] = '        // --- Balances ---';
        foreach ($balances as $balance) {
            $lines[] = '        Balance::create([';
            $lines[] = "            'user_id' => \$user->id,";
            $lines[] = "            'date' => ".$this->export($balance->getRawOriginal('date')).',';
            $lines[] = "            'total_equity' => ".$this->export($balance->total_equity).',';
            $lines[] = "            'wallet_balance' => ".$this->export($balance->wallet_balance).',';
            $lines[] = "            'cum_realised_pnl' => ".$this->export($balance->cum_realised_pnl).',';
            $lines[] = "            'is_demo' => ".$this->export($balance->is_demo).',';
            $lines[] = "            'market' => ".$this->export($balance->market).',';
            $lines[] = '        ]);';
        }

        $lines[] = '    }';
        $lines[] = '}';
        $lines[] = '';

        return implode("\n", $lines);
    }

    // Safely export a PHP value to a string representation for code generation.
    private function export(mixed $value): string
    {
        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        // Escape single quotes inside string values
        return "'".addcslashes((string) $value, "'\\")."'";
    }
}
