<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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

    public function getLocalDateAttribute(): string
    {
        // $this->date is already a Carbon instance in app timezone (via $casts)
        return $this->date->format('M d, Y');
    }
}
