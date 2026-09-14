<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvoiceType: string implements HasLabel
{
    case Rent = 'rent';
    case Deposit = 'deposit';
    case LateFee = 'late_fee';
    case Utility = 'utility';
    case Maintenance = 'maintenance';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rent => 'Rent',
            self::Deposit => 'Security deposit',
            self::LateFee => 'Late fee',
            self::Utility => 'Utility',
            self::Maintenance => 'Maintenance',
            self::Other => 'Other',
        };
    }
}
