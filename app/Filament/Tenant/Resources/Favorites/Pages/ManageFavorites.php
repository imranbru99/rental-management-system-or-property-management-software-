<?php

namespace App\Filament\Tenant\Resources\Favorites\Pages;

use App\Filament\Tenant\Resources\Favorites\FavoriteResource;
use Filament\Resources\Pages\ManageRecords;

class ManageFavorites extends ManageRecords
{
    protected static string $resource = FavoriteResource::class;
}
