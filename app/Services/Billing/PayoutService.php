<?php

namespace App\Services\Billing;

use App\Enums\PaymentStatus;
use App\Enums\PayoutStatus;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Payout;

class PayoutService
{
    public function schedule(Organization $organization, ?string $destination = null): Payout
    {
        $gross = (int) Payment::query()
            ->where('organization_id', $organization->id)
            ->where('status', PaymentStatus::Completed)
            ->sum('amount');

        $alreadyPaid = (int) Payout::query()
            ->where('organization_id', $organization->id)
            ->whereIn('status', [PayoutStatus::Scheduled, PayoutStatus::Processing, PayoutStatus::Paid])
            ->sum('gross_amount');

        $available = max(0, $gross - $alreadyPaid);
        $feePercent = (float) config('rentos.platform_fee_percent', 2.5);
        $fee = (int) round($available * ($feePercent / 100));

        return Payout::query()->create([
            'organization_id' => $organization->id,
            'gross_amount' => $available,
            'platform_fee' => $fee,
            'net_amount' => max(0, $available - $fee),
            'currency' => $organization->currency ?? config('rentos.currency'),
            'status' => PayoutStatus::Scheduled,
            'destination' => $destination ?: 'bank',
            'scheduled_for' => now()->addDay(),
        ]);
    }

    public function markPaid(Payout $payout): Payout
    {
        $payout->update([
            'status' => PayoutStatus::Paid,
            'paid_at' => now(),
        ]);

        return $payout->refresh();
    }
}
