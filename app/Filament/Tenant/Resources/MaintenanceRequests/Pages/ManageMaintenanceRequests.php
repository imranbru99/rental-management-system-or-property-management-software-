<?php

namespace App\Filament\Tenant\Resources\MaintenanceRequests\Pages;

use App\Filament\Tenant\Resources\MaintenanceRequests\MaintenanceRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMaintenanceRequests extends ManageRecords
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['tenant_id'] = auth()->id();
                    $lease = auth()->user()?->leases()->where('property_id', $data['property_id'] ?? null)->first();
                    $data['organization_id'] = $lease?->organization_id;
                    $data['lease_id'] = $lease?->id;

                    return $data;
                }),
        ];
    }
}
