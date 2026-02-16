<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartAnalysis extends Model
{
    protected $fillable = [
        'trade_id',
        'analysis',
    ];
}
