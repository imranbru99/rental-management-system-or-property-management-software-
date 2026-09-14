<?php

namespace App\Filament\Tenant\Resources\Documents\Pages;

use App\Filament\Tenant\Resources\Documents\DocumentResource;
use App\Models\Lease;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDocuments extends ManageRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $lease = Lease::query()->find($data['documentable_id']);
                    $data['organization_id'] = $lease?->organization_id;
                    $data['documentable_type'] = Lease::class;
                    $data['uploaded_by'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
