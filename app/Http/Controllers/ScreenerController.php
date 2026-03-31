<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ScreenerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ScreenerController extends Controller
{
    public function __construct(
        private readonly ScreenerService $screenerService
    ) {}

    // Renders scraener page with filtered results based on technical indicators
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'timeframe' => 'sometimes|string|in:1h,4h,1D,1W',
            'price_min' => 'sometimes|nullable|numeric|min:0',
            'price_max' => 'sometimes|nullable|numeric|min:0',
            'change_min' => 'sometimes|nullable|numeric',
            'change_max' => 'sometimes|nullable|numeric',
            'volume_min' => 'sometimes|nullable|numeric|min:0',
            'sort_by' => 'sometimes|string',
            'sort_dir' => 'sometimes|string|in:asc,desc',
            'indicators' => 'sometimes|array',
            'indicators.*.key' => 'required_with:indicators|string',
            'indicators.*.operator' => 'sometimes|nullable|string|in:gt,gte,lt,lte,eq,between',
            'indicators.*.value' => 'sometimes|nullable|numeric',
            'indicators.*.value_max' => 'sometimes|nullable|numeric',
            'indicators.*.condition' => 'sometimes|nullable|string',
        ]);

        $hasFilters = $request->has('timeframe') || $request->has('indicators');
        $results = [];
        $total = 0;

        if ($hasFilters) {
            $screenData = $this->screenerService->screen($validated);
            $results = $screenData['results'];
            $total = $screenData['total'];
        }

        $indicatorConditions = [
            'sma' => ScreenerService::getConditionsForType('sma'),
            'ema' => ScreenerService::getConditionsForType('ema'),
            'rsi' => ScreenerService::getConditionsForType('rsi'),
            'macd' => ScreenerService::getConditionsForType('macd'),
            'bb' => ScreenerService::getConditionsForType('bb'),
            'volume_sma' => ScreenerService::getConditionsForType('volume_sma'),
            'support' => ScreenerService::getConditionsForType('support'),
            'resistance' => ScreenerService::getConditionsForType('resistance'),
            'price_change' => ScreenerService::getConditionsForType('price_change'),
            'volume_basic' => ScreenerService::getConditionsForType('volume_basic'),
            'turnover_basic' => ScreenerService::getConditionsForType('turnover_basic'),
        ];

        return view('screener.index', [
            'results' => $results,
            'total' => $total,
            'hasFilters' => $hasFilters,
            'filters' => $validated,
            'availableIndicators' => ScreenerService::availableIndicators(),
            'indicatorConditions' => $indicatorConditions,
            'availableTimeframes' => ScreenerService::availableTimeframes(),
        ]);
    }
}
