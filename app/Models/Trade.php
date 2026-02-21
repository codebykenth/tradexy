<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    protected $fillable = [
        'user_id',
        'strategy_id',
        'order_id',
        'symbol',
        'entry_side',
        'exit_side',
        'entry_price',
        'exit_price',
        'quantity',
        'cum_entry_value',
        'cum_exit_value',
        'avg_entry_price',
        'avg_exit_price',
        'entry_emotion',
        'exit_emotion',
        'take_profit_price',
        'stop_loss_price',
        'timeframe',
        'leverage',
        'chart_picture',
        'open_fees',
        'close_fees',
        'closed_pnl',
        'total_pnl',
        'open_datetime',
        'close_datetime',
        'ai_analysis'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function strategy(): HasMany
    {
        return $this->hasMany(Strategy::class);
    }

    public function getDirectChartUrlAttribute()
    {
        $url = $this->chart_picture;

        if ($url && str_contains($url, 'drive.google.com')) {
            // Extract ID
            if (preg_match('/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                $fileId = $matches[1];

                // Bypasses the 403 Forbidden error
                return "https://lh3.googleusercontent.com/d/{$fileId}";
            }
        }

        return $url;
    }
}
