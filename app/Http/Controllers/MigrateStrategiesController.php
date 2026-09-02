<?php

namespace App\Http\Controllers;

use App\Models\Strategy;
use App\Models\StrategyRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MigrateStrategiesController extends Controller
{
    public function migrate()
    {
        $apiUrl = config('services.old_journal.url');
        $apiToken = config('services.old_journal.token');

        $response = Http::withToken($apiToken)->get("$apiUrl/migrate-strategy")->json();

        $strategies = $response['strategies'];

        // dd($strategies);
        // Maps old API field names to strategy_rules type enum
        $ruleTypeMap = [
            'entry_rules' => 'entry',
            'exit_rules' => 'exit',
            'risk_management' => 'risk_management',
            'stop_loss_rules' => 'risk_management',
            'scaling_rules' => 'scaling',
        ];

        // Temporarily allow mass-assigning 'id' to preserve original API IDs
        Strategy::unguard();

        foreach ($strategies as $strategy) {
            $newStrategy = Strategy::updateOrCreate(
                ['id' => $strategy['id']],
                [
                    'user_id' => Auth::id(),
                    'name' => $strategy['name'],
                    'description' => $strategy['description'],
                    'status' => $strategy['status'] ?? 'inactive',
                    'category' => $strategy['category'],
                    'markets' => $strategy['markets'],
                    'timeframes' => $strategy['timeframes'],
                    'target_rr' => $strategy['target_rr'] ?? 0,
                    'max_risk_per_trade' => $strategy['max_risk_per_trade'] ?? 0,
                    'color' => $strategy['color'] ?? '#000000',
                ]
            );

            // Delete existing rules then recreate (idempotent sync)
            $newStrategy->rules()->delete();

            $order = 0;
            foreach ($ruleTypeMap as $apiField => $ruleType) {
                if (!empty($strategy[$apiField])) {
                    $fieldValue = $strategy[$apiField];

                    $rulesList = is_array($fieldValue)
                        ? $fieldValue
                        : preg_split('/\r\n|\r|\n/', (string) $fieldValue);

                    foreach (array_filter(array_map('trim', $rulesList)) as $ruleText) {
                        StrategyRules::create([
                            'strategy_id' => $newStrategy->id,
                            'type' => $ruleType,
                            'rule' => $ruleText,
                            'order' => $order++,
                        ]);
                    }
                }
            }
        }

        Strategy::reguard();

        return response()->json([
            'message' => 'Migration complete!',
            'total_from_api' => count($strategies),
        ]);
    }
}
