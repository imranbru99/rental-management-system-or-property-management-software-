<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UnitSpaceType: string implements HasColor, HasLabel
{
    case Room = 'room';
    case Bath = 'bath';
    case Balcony = 'balcony';
    case Kitchen = 'kitchen';
    case Dining = 'dining';
    case Living = 'living';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Room => 'Room',
            self::Bath => 'Bath',
            self::Balcony => 'Balcony',
            self::Kitchen => 'Kitchen',
            self::Dining => 'Dining',
            self::Living => 'Living',
            self::Other => 'Other',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Room => 'info',
            self::Bath => 'primary',
            self::Balcony => 'success',
            self::Kitchen => 'warning',
            self::Dining => 'danger',
            self::Living => 'gray',
            self::Other => 'slate',
        };
    }
}
