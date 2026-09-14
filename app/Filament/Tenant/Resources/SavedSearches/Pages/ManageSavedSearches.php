<?php

namespace App\Filament\Tenant\Resources\SavedSearches\Pages;

use App\Filament\Tenant\Resources\SavedSearches\SavedSearchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSavedSearches extends ManageRecords
{
    protected static string $resource = SavedSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
