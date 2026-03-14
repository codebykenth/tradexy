<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    protected $fillable = [
        'user_id',
        'strategy_id',
        'order_id',
        'market',
        'symbol',
        'entry_side',
        'exit_side',
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
        'broker_commission', // 0.25% of gross value	
        'pse_trans_fee', // 0.005% of gross value	
        'sccp_fee', // 0.01% of gross value	
        'pse_vat', // 12% of the commission	
        'sales_tax', // 0.1% of gross value
        'closed_pnl',
        'total_pnl',
        'open_datetime',
        'close_datetime',
        'ai_analysis',
        'share_token',
    ];

    protected $casts = [
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function reasons(): HasMany
    {
        return $this->hasMany(Reason::class);
    }

    public function getDirectChartUrlAttribute()
    {
        $url = $this->chart_picture;

        if ($url && str_contains($url, 'drive.google.com')) {
            // Extract ID
            if (preg_match('/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                $fileId = $matches[1];

                // Bypasses the 403 Forbidden error
                return "https://lh3.googleusercontent.com/d/{$fileId}?scale=1&.png";
            }
        }

        return $url;
    }

    public function getSessionAttribute()
    {
        if (!$this->open_datetime)
            return 'N/A';

        // PSE trades always run during the PSE session (9:30 AM – 3:30 PM PHT)
        if ($this->market === 'pse') {
            return 'PSE Session';
        }

        // DB stores UTC — parse as UTC to determine session
        $hour = \Carbon\Carbon::parse($this->open_datetime, 'UTC')->hour;

        if ($hour >= 13 && $hour < 17) {
            return 'Overlap (London & NY)';
        } elseif ($hour >= 13 && $hour < 22) {
            return 'New York';
        } elseif ($hour >= 8 && $hour < 13) {
            return 'London';
        } elseif ($hour >= 0 && $hour < 8) {
            return 'Asian';
        }

        return 'Asian / Sydney';
    }

    public function getDurationAttribute()
    {
        if (!$this->open_datetime || !$this->close_datetime) {
            return 'N/A';
        }

        $open = Carbon::parse($this->open_datetime, 'UTC');
        $close = Carbon::parse($this->close_datetime, 'UTC');

        // Return the human-readable difference without the "ago" / "after" suffix
        return $open->diffForHumans($close, true);
    }

    public function getRiskRewardAttribute()
    {
        if (!$this->avg_entry_price || !$this->stop_loss_price) {
            return 'N/A';
        }

        $entry = (float) $this->avg_entry_price;
        $sl = (float) $this->stop_loss_price;
        $risk = abs($entry - $sl);

        if ($risk == 0) {
            return 'N/A';
        }

        $isShort = strtolower($this->entry_side) === 'short';

        // Use actual exit if trade is closed, otherwise use planned take profit
        if ($this->avg_exit_price) {
            $exit = (float) $this->avg_exit_price;
            $reward = $isShort ? ($entry - $exit) : ($exit - $entry);
        } elseif ($this->take_profit_price) {
            $tp = (float) $this->take_profit_price;
            $reward = $isShort ? ($entry - $tp) : ($tp - $entry);
        } else {
            return 'N/A';
        }

        $rr = $reward / $risk;
        return number_format($rr, 2) . 'R';
    }
}
