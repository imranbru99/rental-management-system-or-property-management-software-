<?php

namespace App\Filament\Tenant\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $due = Invoice::query()
            ->where('payer_id', $user?->id)
            ->whereIn('status', [InvoiceStatus::Open, InvoiceStatus::Partial, InvoiceStatus::Overdue])
            ->sum('total');

        return [
            Stat::make('Active leases', $user?->leases()->count() ?? 0),
            Stat::make('Balance due', Money::format((int) $due, 'BDT')),
            Stat::make('Maintenance tickets', MaintenanceRequest::query()->where('tenant_id', $user?->id)->count()),
        ];
    }
}
