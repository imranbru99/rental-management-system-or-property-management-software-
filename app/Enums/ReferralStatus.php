<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReferralStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Qualified = 'qualified';
    case Credited = 'credited';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Qualified => 'Qualified',
            self::Credited => 'Credited',
            self::Expired => 'Expired',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Qualified => 'info',
            self::Credited => 'success',
            self::Expired => 'gray',
        };
    }
}
