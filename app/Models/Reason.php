<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $fillable = [
        'trade_id',
        'type', // 'entry' or 'exit'
        'reason',
        'is_primary',
    ];
}
