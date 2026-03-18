<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Balance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'total_equity',
        'wallet_balance',
        'cum_realised_pnl',
        'is_demo',
        'market',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_demo' => 'boolean',
    ];

    protected static function booted(): void
    {
        $bustCache = function (self $balance) {
            if ($balance->user_id) {
                Cache::put("trades_version_user_{$balance->user_id}", microtime(true));
            }
        };

        static::created($bustCache);
        static::updated($bustCache);
        static::deleted($bustCache);
    }

    public function getLocalDateAttribute(): string
    {
        // $this->date is already a Carbon instance in app timezone (via $casts)
        return $this->date->format('M d, Y');
    }
}
