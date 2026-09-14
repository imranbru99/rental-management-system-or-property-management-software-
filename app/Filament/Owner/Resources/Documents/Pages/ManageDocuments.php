<?php

namespace App\Filament\Owner\Resources\Documents\Pages;

use App\Filament\Owner\Resources\Documents\DocumentResource;
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
                    $data['uploaded_by'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
