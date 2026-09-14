<?php

namespace App\Filament\Tenant\Resources\Reviews\Pages;

use App\Filament\Tenant\Resources\Reviews\ReviewResource;
use App\Models\Property;
use App\Services\Reviews\ReviewService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageReviews extends ManageRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data, ReviewService $reviews) {
                    $property = Property::query()->findOrFail($data['reviewee_id']);

                    return $reviews->submit([
                        'organization_id' => $property->organization_id,
                        'reviewer_id' => auth()->id(),
                        'reviewee_type' => Property::class,
                        'reviewee_id' => $property->id,
                        'rating' => $data['rating'],
                        'body' => $data['body'] ?? null,
                    ]);
                }),
        ];
    }
}
