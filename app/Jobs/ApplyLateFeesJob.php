<?php

namespace App\Jobs;

use App\Services\Billing\BillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ApplyLateFeesJob implements ShouldQueue
{
    use Queueable;

    public function handle(BillingService $billing): void
    {
        $billing->applyLateFees();
    }
}
