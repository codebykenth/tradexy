<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'trade_id',
        'lesson',
        'category', // 'strategy', 'execution', 'risk_management', 'psychology', etc.
        'is_positive'
    ];
}
