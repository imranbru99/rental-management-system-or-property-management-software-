<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Invited = 'invited';
    case Suspended = 'suspended';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Invited => 'warning',
            self::Suspended => 'danger',
            self::Closed => 'gray',
        };
    }
}
