<?php

namespace App\Filament\Owner\Resources\LeaseTemplates\Pages;

use App\Filament\Owner\Resources\LeaseTemplates\LeaseTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLeaseTemplates extends ManageRecords
{
    protected static string $resource = LeaseTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
