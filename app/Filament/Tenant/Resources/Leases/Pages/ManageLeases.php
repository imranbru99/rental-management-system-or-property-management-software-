<?php

namespace App\Filament\Tenant\Resources\Leases\Pages;

use App\Filament\Tenant\Resources\Leases\LeaseResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLeases extends ManageRecords
{
    protected static string $resource = LeaseResource::class;
}
