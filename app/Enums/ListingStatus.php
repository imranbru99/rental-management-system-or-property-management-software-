<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ListingStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Rejected = 'rejected';
    case Unlisted = 'unlisted';
    case Leased = 'leased';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending review',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
            self::Unlisted => 'Unlisted',
            self::Leased => 'Leased',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::PendingReview => 'warning',
            self::Published => 'success',
            self::Rejected => 'danger',
            self::Unlisted => 'slate',
            self::Leased => 'info',
        };
    }
}
