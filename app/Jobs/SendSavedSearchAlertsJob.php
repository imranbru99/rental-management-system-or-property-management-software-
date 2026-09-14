<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Models\SavedSearch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSavedSearchAlertsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        SavedSearch::query()
            ->where('alert_frequency', '!=', 'off')
            ->each(function (SavedSearch $search): void {
                $criteria = $search->criteria ?? [];
                $matches = Listing::query()
                    ->published()
                    ->when($criteria['q'] ?? null, function ($query, string $q): void {
                        $query->where(function ($inner) use ($q): void {
                            $inner->where('title', 'like', "%{$q}%")
                                ->orWhereHas('property', fn ($property) => $property->where('city', 'like', "%{$q}%"));
                        });
                    })
                    ->when($criteria['min_bedrooms'] ?? null, fn ($query, $beds) => $query->whereHas('property', fn ($p) => $p->where('bedrooms', '>=', $beds)))
                    ->when($criteria['pets'] ?? false, fn ($query) => $query->whereHas('property', fn ($p) => $p->where('pet_friendly', true)))
                    ->when($criteria['furnished'] ?? false, fn ($query) => $query->whereHas('property', fn ($p) => $p->where('furnished', true)))
                    ->when($criteria['max_rent'] ?? null, fn ($query, $rent) => $query->where('rent', '<=', (int) $rent))
                    ->when($search->last_alerted_at, fn ($query, $since) => $query->where('published_at', '>', $since))
                    ->count();

                if ($matches > 0) {
                    $search->update(['last_alerted_at' => now()]);
                }
            });
    }
}
