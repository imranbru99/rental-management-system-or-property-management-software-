<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Listings\ListingModerationResource;
use Filament\Resources\Pages\ManageRecords;

class ManageListingModeration extends ManageRecords
{
    protected static string $resource = ListingModerationResource::class;
}
