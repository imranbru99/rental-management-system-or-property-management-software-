<?php

namespace App\Filament\Owner\Resources\Properties\Pages;

use App\Filament\Owner\Actions\InviteTenantAction;
use App\Filament\Owner\Resources\Properties\PropertyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageProperties extends ManageRecords
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            InviteTenantAction::make(name: 'inviteAnyTenant'),
            CreateAction::make()
                ->label('Add building')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
