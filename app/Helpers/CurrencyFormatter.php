<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Services\CurrencyService;

final class CurrencyFormatter
{
    /**
     * Format an amount based on the user's preferred currency and the trade's market.
     */
    public static function format(mixed $amount, string $market): string
    {
        if ($amount === null) {
            return '-';
        }

        $service = app(CurrencyService::class);
        $preferred = session('preferred_currency', 'USD');

        $result = $service->convert((float) $amount, $market, $preferred);

        return $result['symbol'].' '.number_format($result['value'], 2);
    }

    /**
     * Get the numeric normalized value based on currency logic.
     */
    public static function normalizeValueAmount(mixed $amount, string $market): float
    {
        if ($amount === null) {
            return 0.0;
        }

        $service = app(CurrencyService::class);
        $preferred = session('preferred_currency', 'USD');

        $result = $service->convert((float) $amount, $market, $preferred);

        return (float) $result['value'];
    }
}
