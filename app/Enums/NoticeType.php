<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NoticeType: string implements HasLabel
{
    case Vacate = 'vacate';
    case RenewalOffer = 'renewal_offer';
    case RentIncrease = 'rent_increase';
    case Entry = 'entry';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Vacate => 'Notice to vacate',
            self::RenewalOffer => 'Renewal offer',
            self::RentIncrease => 'Rent increase',
            self::Entry => 'Notice of entry',
            self::Other => 'Other',
        };
    }
}
