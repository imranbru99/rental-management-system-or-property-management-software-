<?php

namespace App\Services\Ai;

use App\Models\Listing;
use App\Models\Property;
use ImranDevBd\AiHub\Facades\AIHub;
use Throwable;

class ListingAiService
{
    /**
     * @return array{title: string, description: string}
     */
    public function generateCopy(Property $property): array
    {
        $prompt = <<<PROMPT
Write a rental listing for this property as JSON with keys title and description.
Name: {$property->name}
Type: {$property->type->getLabel()}
Location: {$property->full_address}
Beds: {$property->bedrooms}, Baths: {$property->bathrooms}
Rent: {$property->base_rent} {$property->currency} (amount is in minor units / cents)
Pets: {$property->pet_friendly}
Furnished: {$property->furnished}
Amenities: {$this->amenities($property)}
Keep the tone professional and local-market friendly. Description 120-180 words.
PROMPT;

        try {
            $data = AIHub::prompt($prompt)
                ->forJob('listing-copy')
                ->recoverJson()
                ->send()
                ->json();

            return [
                'title' => (string) ($data['title'] ?? $property->name),
                'description' => (string) ($data['description'] ?? $property->description),
            ];
        } catch (Throwable) {
            return [
                'title' => $property->name.' in '.$property->city,
                'description' => $property->description ?: 'A well-located rental ready for a new tenant.',
            ];
        }
    }

    public function suggestRent(Property $property): ?int
    {
        $prompt = <<<PROMPT
Suggest a monthly market rent as JSON {"rent_minor_units": number} for:
City: {$property->city}, Country: {$property->country}
Beds: {$property->bedrooms}, Baths: {$property->bathrooms}
Current asking (minor units): {$property->base_rent} {$property->currency}
Return only JSON.
PROMPT;

        try {
            $data = AIHub::prompt($prompt)
                ->forJob('dynamic-pricing')
                ->recoverJson()
                ->send()
                ->json();

            return isset($data['rent_minor_units']) ? (int) $data['rent_minor_units'] : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function fillListing(Listing $listing): Listing
    {
        $copy = $this->generateCopy($listing->property);
        $listing->fill([
            'title' => $copy['title'],
            'description' => $copy['description'],
            'ai_generated' => true,
        ])->save();

        return $listing->refresh();
    }

    protected function amenities(Property $property): string
    {
        return collect($property->amenities ?? [])->implode(', ') ?: 'none listed';
    }
}
