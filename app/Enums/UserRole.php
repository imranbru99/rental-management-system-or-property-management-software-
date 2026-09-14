<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case SuperAdmin = 'super_admin';
    case OrgAdmin = 'org_admin';
    case Owner = 'owner';
    case Assistant = 'assistant';
    case Tenant = 'tenant';
    case Vendor = 'vendor';
    case Agent = 'agent';
    case Guarantor = 'guarantor';
    case Inspector = 'inspector';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::OrgAdmin => 'Organization Admin',
            self::Owner => 'Home Owner',
            self::Assistant => 'Property Manager',
            self::Tenant => 'Tenant',
            self::Vendor => 'Vendor',
            self::Agent => 'Agent / Broker',
            self::Guarantor => 'Guarantor',
            self::Inspector => 'Inspector',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::OrgAdmin => 'warning',
            self::Owner => 'primary',
            self::Assistant => 'info',
            self::Tenant => 'success',
            self::Vendor => 'gray',
            self::Agent => 'purple',
            self::Guarantor => 'slate',
            self::Inspector => 'indigo',
        };
    }

    public function canAccessOwnerPanel(): bool
    {
        return in_array($this, [self::SuperAdmin, self::OrgAdmin, self::Owner, self::Assistant, self::Agent], true);
    }

    public function canAccessTenantPanel(): bool
    {
        return in_array($this, [self::Tenant, self::Guarantor], true);
    }
}
