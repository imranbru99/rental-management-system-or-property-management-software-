<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PropertyType: string implements HasLabel
{
    case SingleFamily = 'single_family';
    case Apartment = 'apartment';
    case Condo = 'condo';
    case Townhouse = 'townhouse';
    case Room = 'room';
    case Studio = 'studio';
    case Commercial = 'commercial';

    public function getLabel(): string
    {
        return match ($this) {
            self::SingleFamily => 'Single-family home',
            self::Apartment => 'Apartment',
            self::Condo => 'Condo',
            self::Townhouse => 'Townhouse',
            self::Room => 'Room',
            self::Studio => 'Studio',
            self::Commercial => 'Commercial',
        };
    }
}
