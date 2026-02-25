<?php

namespace App\Models;

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
}
