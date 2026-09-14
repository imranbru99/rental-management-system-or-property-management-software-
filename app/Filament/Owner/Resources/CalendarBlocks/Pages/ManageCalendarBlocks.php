<?php

namespace App\Filament\Owner\Resources\CalendarBlocks\Pages;

use App\Filament\Owner\Resources\CalendarBlocks\CalendarBlockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCalendarBlocks extends ManageRecords
{
    protected static string $resource = CalendarBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
