<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum NoticeStatus: string implements HasColor, HasLabel
{
    case Submitted = 'submitted';
    case Acknowledged = 'acknowledged';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Acknowledged => 'Acknowledged',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Submitted => 'warning',
            self::Acknowledged => 'info',
            self::Accepted => 'success',
            self::Declined => 'danger',
            self::Cancelled => 'gray',
        };
    }
}
