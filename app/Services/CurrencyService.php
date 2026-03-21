<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class CurrencyService
{
    private const CACHE_KEY = 'usd_php_rate';

    private const CACHE_TTL = 3600 * 12; // 12 hours

    /**
     * Get the current USD to PHP exchange rate.
     */
    public function getRate(): float
    {
        return (float) Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                // Using fawazahmed0/currency-api as a free, no-key fallback (updated daily)
                $response = Http::get('https://latest.currency-api.pages.dev/v1/currencies/usd.json');

                if ($response->successful()) {
                    return (float) ($response->json()['usd']['php'] ?? 57.0);
                }
            } catch (\Exception $e) {
                // Fallback to a reasonable default if API fails
            }

            return 57.0;
        });
    }

    /**
     * Convert an amount based on the user's preference and the trade's origin market.
     *
     * @param  float  $amount  The raw amount from the DB
     * @param  string  $market  The market of the trade ('crypto' = USD, 'pse' = PHP)
     * @param  string  $targetCurrency  The user's preferred currency ('USD', 'PHP')
     */
    public function convert(float $amount, string $market, string $targetCurrency): array
    {
        $rate = $this->getRate();
        $isCrypto = ($market === 'crypto');

        // Logical cross-conversion
        if ($targetCurrency === 'PHP') {
            $converted = $isCrypto ? ($amount * $rate) : $amount;

            return ['value' => $converted, 'symbol' => '₱'];
        }

        // Default to USD
        $converted = $isCrypto ? $amount : ($amount / $rate);

        return ['value' => $converted, 'symbol' => '$'];
    }
}
