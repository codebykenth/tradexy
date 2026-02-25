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
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function getLocalDateAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['date'], 'UTC')
            ->timezone(config('app.timezone'))
            ->format('M d, Y');
    }
}
