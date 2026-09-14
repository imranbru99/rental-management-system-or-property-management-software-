<?php

namespace App\Filament\Tenant\Resources\Applications\Pages;

use App\Filament\Tenant\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ManageRecords;

class ManageApplications extends ManageRecords
{
    protected static string $resource = ApplicationResource::class;
}
