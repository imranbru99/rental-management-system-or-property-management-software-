<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum VendorCategory: string implements HasLabel
{
    case Plumbing = 'plumbing';
    case Electrical = 'electrical';
    case Hvac = 'hvac';
    case Cleaning = 'cleaning';
    case Pest = 'pest';
    case Appliances = 'appliances';
    case General = 'general';
    case Landscaping = 'landscaping';
    case Security = 'security';

    public function getLabel(): string
    {
        return match ($this) {
            self::Plumbing => 'Plumbing',
            self::Electrical => 'Electrical',
            self::Hvac => 'HVAC',
            self::Cleaning => 'Cleaning',
            self::Pest => 'Pest control',
            self::Appliances => 'Appliances',
            self::General => 'General contractor',
            self::Landscaping => 'Landscaping',
            self::Security => 'Security',
        };
    }
}
