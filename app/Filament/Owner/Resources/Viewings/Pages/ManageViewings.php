<?php

namespace App\Filament\Owner\Resources\Viewings\Pages;

use App\Filament\Owner\Resources\Viewings\ViewingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageViewings extends ManageRecords
{
    protected static string $resource = ViewingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
