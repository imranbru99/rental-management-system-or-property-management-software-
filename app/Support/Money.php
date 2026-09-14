<?php

namespace App\Support;

class Money
{
    public static function format(int $minorUnits, string $currency = 'BDT'): string
    {
        $amount = number_format($minorUnits / 100, 2);

        return match (strtoupper($currency)) {
            'BDT' => '৳'.$amount,
            'USD' => '$'.$amount,
            'AUD' => 'A$'.$amount,
            'EUR' => '€'.$amount,
            'GBP' => '£'.$amount,
            'INR' => '₹'.$amount,
            default => $currency.' '.$amount,
        };
    }

    public static function fromMajor(float|int|string $major): int
    {
        return (int) round(((float) $major) * 100);
    }

    public static function toMajor(int $minorUnits): float
    {
        return round($minorUnits / 100, 2);
    }
}
