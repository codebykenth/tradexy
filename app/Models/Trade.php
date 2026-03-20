<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $trade_count
 * @property int $trades_count
 * @property int $win_count
 * @property int $has_ai_analysis
 * @property float $win_rate
 * @property float $net_pnl
 * @property float $total_win_amount
 * @property float $total_loss_amount
 * @property string $human_time
 * @property string $formatted_pnl
 * @property string $direct_chart_url
 * @property string $session
 * @property string $duration
 * @property string $risk_reward
 * @property \Carbon\Carbon $close_datetime
 * @property \Carbon\Carbon $open_datetime
 */
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
        'is_demo',
    ];

    protected $casts = [
        'is_demo' => 'boolean',
        'open_datetime' => 'datetime',
        'close_datetime' => 'datetime',
    ];

    protected static function booted(): void
    {
        $bustCache = function (self $trade) {
            if ($trade->user_id) {
                Cache::put("trades_version_user_{$trade->user_id}", microtime(true));
            }
        };

        static::created($bustCache);
        static::updated($bustCache);
        static::deleted($bustCache);
    }

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

        if (!$url) {
            return null;
        }

        // 1. Handle Google Drive legacy URLs (lh3.googleusercontent.com)
        if (str_contains($url, 'drive.google.com')) {
            if (preg_match('/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                $fileId = $matches[1];

                return "https://lh3.googleusercontent.com/d/{$fileId}?scale=1&.png";
            }
        }

        // 2. Fix Duplicated Bucket Paths (common Firebase/GCS migration issue)
        // This regex collapses segments like /bucket/bucket/ into /bucket/
        $url = preg_replace('/(storage\.googleapis\.com\/([^\/]+))\/\2\//', '$1/', $url);

        // 3. GCS to CDN Swap
        $cdnBase = config('filesystems.disks.gcs.url');
        $bucket = config('filesystems.disks.gcs.bucket');

        if ($cdnBase && str_contains($url, 'storage.googleapis.com')) {
            // Replace 'https://storage.googleapis.com/your-bucket' with 'https://cdn.site.com/'
            $search = "https://storage.googleapis.com/{$bucket}";
            $cleanCDN = rtrim($cdnBase, '/').'/';

            return str_replace($search.'/', $cleanCDN, $url);
        }

        return $url;
    }

    public function getSessionAttribute()
    {
        if (!$this->open_datetime) {
            return 'N/A';
        }

        // PSE trades always run during the PSE session (9:30 AM – 3:30 PM PHT)
        if ($this->market === 'pse') {
            return 'PSE Session';
        }

        // DB stores UTC — parse as UTC to determine session
        $hour = Carbon::parse($this->open_datetime, 'UTC')->hour;

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
        return $open->diffForHumans($close, \Carbon\CarbonInterface::DIFF_ABSOLUTE);
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

        return number_format($rr, 2).'R';
    }

    public function getFormattedPnlAttribute(): string
    {
        return number_format($this->total_pnl, 2);
    }

    public function getTotalFeesAttribute(): float
    {
        if ($this->market === 'pse') {
            return (float) ($this->broker_commission ?? 0) +
                (float) ($this->pse_trans_fee ?? 0) +
                (float) ($this->sccp_fee ?? 0) +
                (float) ($this->pse_vat ?? 0) +
                (float) ($this->sales_tax ?? 0);
        }

        return (float) ($this->open_fees ?? 0) + (float) ($this->close_fees ?? 0);
    }
}
