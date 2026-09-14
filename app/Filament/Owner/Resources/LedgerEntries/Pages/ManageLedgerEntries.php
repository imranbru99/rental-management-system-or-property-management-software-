<?php

namespace App\Filament\Owner\Resources\LedgerEntries\Pages;

use App\Filament\Owner\Resources\LedgerEntries\LedgerEntryResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLedgerEntries extends ManageRecords
{
    protected static string $resource = LedgerEntryResource::class;
}
