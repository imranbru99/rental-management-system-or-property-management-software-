<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MaintenancePriority: string implements HasColor, HasLabel
{
    case Emergency = 'emergency';
    case Urgent = 'urgent';
    case Routine = 'routine';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Emergency => 'danger',
            self::Urgent => 'warning',
            self::Routine => 'info',
        };
    }

    public function slaHours(): int
    {
        return match ($this) {
            self::Emergency => 4,
            self::Urgent => 24,
            self::Routine => 72,
        };
    }
}
