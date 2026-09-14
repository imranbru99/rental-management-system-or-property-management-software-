<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Card = 'card';
    case Bank = 'bank';
    case Bkash = 'bkash';
    case Nagad = 'nagad';
    case SslCommerz = 'sslcommerz';
    case Cash = 'cash';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::Bank => 'Bank / ACH',
            self::Bkash => 'bKash',
            self::Nagad => 'Nagad',
            self::SslCommerz => 'SSLCommerz',
            self::Cash => 'Cash',
            self::Other => 'Other',
        };
    }
}
