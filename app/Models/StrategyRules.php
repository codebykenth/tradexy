<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrategyRules extends Model
{
    protected $fillable = [
        'strategy_id',
        'type', // 'entry' or 'exit' or 'risk_management' or 'scaling' etc.
        'rule',
        'order', // to maintain the sequence of rules
    ];
}
