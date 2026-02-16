<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
