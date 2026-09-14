<?php

namespace App\Filament\Owner\Widgets;

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Support\Money;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OwnerStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $org = Filament::getTenant();

        $openRent = Invoice::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Overdue, InvoiceStatus::Partial])
            ->sum('total');

        $occupied = Lease::query()
            ->when($org, fn ($q) => $q->where('organization_id', $org->id))
            ->where('status', LeaseStatus::Active)
            ->count();

        $properties = Property::query()->when($org, fn ($q) => $q->where('organization_id', $org->id))->count();

        return [
            Stat::make('Properties', $properties),
            Stat::make('Active leases', $occupied),
            Stat::make('Open receivables', Money::format((int) $openRent, $org?->currency ?? 'BDT')),
            Stat::make(
                'Open maintenance',
                MaintenanceRequest::query()
                    ->when($org, fn ($q) => $q->where('organization_id', $org->id))
                    ->whereNotIn('status', [MaintenanceStatus::Resolved, MaintenanceStatus::Rated, MaintenanceStatus::Cancelled])
                    ->count()
            ),
        ];
    }
}
