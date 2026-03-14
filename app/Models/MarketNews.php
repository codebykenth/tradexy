<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketNews extends Model
{
    protected $fillable = [
        'date_range',
        'ai_analysis',
    ];

    protected $casts = [
        'ai_analysis' => 'array',
    ];
}
