<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeaseStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Sent = 'sent';
    case PartiallySigned = 'partially_signed';
    case Active = 'active';
    case PendingRenewal = 'pending_renewal';
    case Ending = 'ending';
    case Expired = 'expired';
    case Terminated = 'terminated';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent for signature',
            self::PartiallySigned => 'Partially signed',
            self::Active => 'Active',
            self::PendingRenewal => 'Pending renewal',
            self::Ending => 'Ending',
            self::Expired => 'Expired',
            self::Terminated => 'Terminated',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'info',
            self::PartiallySigned => 'warning',
            self::Active => 'success',
            self::PendingRenewal => 'primary',
            self::Ending => 'warning',
            self::Expired => 'slate',
            self::Terminated => 'danger',
        };
    }
}
