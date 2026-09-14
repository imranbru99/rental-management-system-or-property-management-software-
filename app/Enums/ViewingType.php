<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ViewingType: string implements HasLabel
{
    case InPerson = 'in_person';
    case Video = 'video';

    public function getLabel(): string
    {
        return match ($this) {
            self::InPerson => 'In person',
            self::Video => 'Video tour',
        };
    }
}
