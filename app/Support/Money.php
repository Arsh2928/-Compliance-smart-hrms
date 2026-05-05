<?php

namespace App\Support;

use NumberFormatter;

class Money
{
    /**
     * Format a numeric value as INR (₹) in Indian locale.
     */
    public static function inr(float|int|string|null $amount, int $decimals = 2): string
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;

        if (class_exists(NumberFormatter::class)) {
            $fmt = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);
            $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
            $formatted = $fmt->formatCurrency($value, 'INR');
            if (is_string($formatted) && $formatted !== '') {
                return $formatted;
            }
        }

        // Fallback (if intl is not enabled)
        return '₹' . number_format($value, $decimals);
    }
}

