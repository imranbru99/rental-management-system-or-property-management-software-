<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DepositStatus: string implements HasColor, HasLabel
{
    case Held = 'held';
    case PartialRefund = 'partial_refund';
    case Refunded = 'refunded';
    case Forfeited = 'forfeited';

    public function getLabel(): string
    {
        return match ($this) {
            self::Held => 'Held',
            self::PartialRefund => 'Partially refunded',
            self::Refunded => 'Refunded',
            self::Forfeited => 'Forfeited',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Held => 'info',
            self::PartialRefund => 'warning',
            self::Refunded => 'success',
            self::Forfeited => 'danger',
        };
    }
}
