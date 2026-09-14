<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Organization;
use App\Models\Property;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Organizations', Organization::query()->count()),
            Stat::make('Users', User::query()->count()),
            Stat::make('Properties', Property::query()->count()),
            Stat::make('Published listings', Listing::query()->published()->count()),
        ];
    }
}
