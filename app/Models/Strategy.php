<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Strategy extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'status', // 'active', 'inactive', 'testing', etc.
        'color', // for UI tagging
        'target_rr', // target risk-reward ratio
        'max_risk_per_trade', // max risk per trade as a percentage of total equity
        'timeframes', // JSON array of preferred timeframes (e.g., ["1m", "5m", "1h"]),
        'markets', // JSON array of preferred markets (e.g., ["crypto", "forex", "stocks"])
    ];

    protected $casts = [
        'timeframes' => 'array',
        'markets' => 'array',
        'category' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(StrategyRules::class);
    }

    public function getNetPnlAttribute()
    {
        return $this->trades->sum('total_pnl');
    }

    public function getTradesCountAttribute()
    {
        return $this->trades->count();
    }

    public function getTotalWinAmountAttribute()
    {
        return $this->trades->where('total_pnl', '>', 0)->sum('total_pnl');
    }

    public function getTotalLossAmountAttribute()
    {
        return $this->trades->where('total_pnl', '<', 0)->sum('total_pnl');
    }

    public function getAvgWinAttribute()
    {
        return $this->trades->where('total_pnl', '>', 0)->avg('total_pnl');
    }

    public function getAvgLossAttribute()
    {
        return $this->trades->where('total_pnl', '<', 0)->avg('total_pnl');
    }

    public function getHitRatioAttribute()
    {
        $tradesCount = $this->trades->count();
        if ($tradesCount === 0)
            return 0;

        $winningTradesCount = $this->trades->where('total_pnl', '>', 0)->count();
        return ($winningTradesCount / $tradesCount) * 100;
    }

    public function getEdgeRatioAttribute()
    {
        $avgWin = $this->avg_win;
        $avgLoss = abs($this->avg_loss ?? 0);
        if ($avgLoss == 0)
            return 0;

        return $avgWin / $avgLoss;
    }
}
