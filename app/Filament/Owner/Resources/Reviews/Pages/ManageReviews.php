<?php

namespace App\Filament\Owner\Resources\Reviews\Pages;

use App\Filament\Owner\Resources\Reviews\ReviewResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageReviews extends ManageRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['reviewer_id'] = auth()->id();
                    $data['reviewee_type'] = User::class;

                    return $data;
                }),
        ];
    }
}
