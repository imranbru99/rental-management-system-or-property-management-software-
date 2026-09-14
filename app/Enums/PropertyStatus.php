<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PropertyStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Active = 'active';
    case Occupied = 'occupied';
    case Vacant = 'vacant';
    case Maintenance = 'maintenance';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'info',
            self::Occupied => 'success',
            self::Vacant => 'warning',
            self::Maintenance => 'danger',
            self::Archived => 'slate',
        };
    }
}
