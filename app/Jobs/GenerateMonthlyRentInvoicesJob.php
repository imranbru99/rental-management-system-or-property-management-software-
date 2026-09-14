<?php

namespace App\Jobs;

use App\Enums\LeaseStatus;
use App\Models\Lease;
use App\Services\Billing\BillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateMonthlyRentInvoicesJob implements ShouldQueue
{
    use Queueable;

    public function handle(BillingService $billing): void
    {
        Lease::query()
            ->where('status', LeaseStatus::Active)
            ->each(function (Lease $lease) use ($billing): void {
                $exists = $lease->invoices()
                    ->where('type', 'rent')
                    ->whereMonth('issue_date', now()->month)
                    ->whereYear('issue_date', now()->year)
                    ->exists();

                if (! $exists) {
                    $billing->createRentInvoice($lease);
                }
            });
    }
}
