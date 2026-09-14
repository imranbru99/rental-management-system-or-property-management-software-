<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MaintenanceStatus: string implements HasColor, HasLabel
{
    case Submitted = 'submitted';
    case Acknowledged = 'acknowledged';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Rated = 'rated';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Acknowledged => 'Acknowledged',
            self::Scheduled => 'Scheduled',
            self::InProgress => 'In progress',
            self::Resolved => 'Resolved',
            self::Rated => 'Rated',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Submitted => 'gray',
            self::Acknowledged => 'info',
            self::Scheduled => 'primary',
            self::InProgress => 'warning',
            self::Resolved => 'success',
            self::Rated => 'success',
            self::Cancelled => 'slate',
        };
    }
}
