<?php

namespace App\Http\Controllers;

use App\Http\Requests\StrategyRequest;
use App\Models\Strategy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StrategyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::id();
        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');
        $cacheKey = "strategies_user_{$userId}_mode_{$accountMode}_market_{$marketMode}";

        $strategies = Cache::remember($cacheKey, now()->addHours(6), function () use ($userId, $accountMode, $marketMode) {
            $query = Strategy::where('user_id', $userId);

            $tradeFilter = function ($q) use ($accountMode, $marketMode) {
                if ($accountMode !== 'all') {
                    $q->where('is_demo', $accountMode === 'demo');
                }
                if ($marketMode !== 'all') {
                    $q->where('market', $marketMode);
                }
            };

            return $query->withCount(['trades as trades_count' => $tradeFilter])
                ->withSum(['trades as net_pnl' => $tradeFilter], 'total_pnl')
                ->withSum(['trades as total_win_amount' => fn ($q) => $tradeFilter($q->where('total_pnl', '>', 0))], 'total_pnl')
                ->withSum(['trades as total_loss_amount' => fn ($q) => $tradeFilter($q->where('total_pnl', '<', 0))], 'total_pnl')
                ->withAvg(['trades as avg_win' => fn ($q) => $tradeFilter($q->where('total_pnl', '>', 0))], 'total_pnl')
                ->withAvg(['trades as avg_loss' => fn ($q) => $tradeFilter($q->where('total_pnl', '<', 0))], 'total_pnl')
                ->withCount(['trades as winning_trades_count' => fn ($q) => $tradeFilter($q->where('total_pnl', '>', 0))])
                ->orderByDesc('net_pnl')
                ->get();
        });

        return view('strategies.index', compact('strategies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('strategies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StrategyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $strategy = Strategy::create($this->extractStrategyData($validated));
        $this->syncRules($strategy, $validated);

        $this->clearStrategyCache();

        return redirect()->route('strategies.index')->with('success', 'Strategy created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $strategy = $this->getOwnedStrategies($id);
        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');

        $strategy->load(['rules', 'trades' => function ($query) use ($accountMode, $marketMode) {
            if ($accountMode !== 'all') {
                $query->where('is_demo', $accountMode === 'demo');
            }
            if ($marketMode !== 'all') {
                $query->where('market', $marketMode);
            }
            $query->latest('close_datetime');
        }]);

        return view('strategies.show', compact('strategy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $strategy = $this->getOwnedStrategies($id);
        $strategy->load('rules'); // Ensure rules are loaded

        return view('strategies.edit', compact('strategy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StrategyRequest $request, int $id): RedirectResponse
    {
        $strategy = $this->getOwnedStrategies($id);
        $validated = $request->validated();
        $strategy->update($this->extractStrategyData($validated));
        $this->syncRules($strategy, $validated);

        $this->clearStrategyCache();

        return redirect()->route('strategies.index')->with('success', 'Strategy updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $strategy = $this->getOwnedStrategies($id);
        $strategy->rules()->delete();
        $strategy->delete();

        $this->clearStrategyCache();

        return redirect()->route('strategies.index')->with('success', 'Strategy deleted successfully!');
    }

    private function getOwnedStrategies(int $id): Strategy
    {
        return Strategy::where('user_id', Auth::id())->findOrFail($id);
    }

    /**
     * Extract only fields that belong to the Strategy model.
     */
    private function extractStrategyData(array $validated): array
    {
        return array_diff_key($validated, array_flip([
            'entry_rules',
            'exit_rules',
            'risk_management_rules',
            'scaling_rules',
        ]));
    }

    /**
     * Delete existing rules and sync new ones based on the valid request data.
     */
    private function syncRules(Strategy $strategy, array $validated): void
    {
        $strategy->rules()->delete();

        $ruleTypes = [
            'entry_rules' => 'entry',
            'exit_rules' => 'exit',
            'risk_management_rules' => 'risk_management',
            'scaling_rules' => 'scaling',
        ];

        $order = 0;
        foreach ($ruleTypes as $field => $type) {
            if (isset($validated[$field]) && is_array($validated[$field])) {
                // Filter out empty lines sent by form
                $rules = array_filter(array_map('trim', $validated[$field]));

                foreach ($rules as $ruleText) {
                    $strategy->rules()->create([
                        'type' => $type,
                        'rule' => $ruleText,
                        'order' => $order++,
                    ]);
                }
            }
        }
    }

    /**
     * Clear all possible strategy cache permutations for the current user.
     */
    private function clearStrategyCache(): void
    {
        $userId = Auth::id();
        $accountModes = ['real', 'demo', 'all'];
        $marketTypes = ['crypto', 'pse', 'forex', 'stocks', 'indices', 'commodities', 'all'];

        foreach ($accountModes as $mode) {
            foreach ($marketTypes as $market) {
                Cache::forget("strategies_user_{$userId}_mode_{$mode}_market_{$market}");
            }
        }
    }
}
