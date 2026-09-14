<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InspectionType: string implements HasLabel
{
    case MoveIn = 'move_in';
    case MoveOut = 'move_out';
    case Routine = 'routine';
    case Safety = 'safety';

    public function getLabel(): string
    {
        return match ($this) {
            self::MoveIn => 'Move-in',
            self::MoveOut => 'Move-out',
            self::Routine => 'Routine',
            self::Safety => 'Safety / compliance',
        };
    }
}
