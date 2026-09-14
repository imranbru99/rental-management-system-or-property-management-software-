<?php

namespace App\Filament\Tenant\Resources\Viewings\Pages;

use App\Enums\ViewingStatus;
use App\Filament\Tenant\Resources\Viewings\ViewingResource;
use App\Models\Listing;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageViewings extends ManageRecords
{
    protected static string $resource = ViewingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $listing = Listing::query()->find($data['listing_id']);
                    $data['organization_id'] = $listing?->organization_id;
                    $data['property_id'] = $listing?->property_id;
                    $data['applicant_id'] = auth()->id();
                    $data['status'] = ViewingStatus::Requested;

                    return $data;
                }),
        ];
    }
}
