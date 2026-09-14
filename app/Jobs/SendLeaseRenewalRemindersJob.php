<?php

namespace App\Jobs;

use App\Enums\LeaseStatus;
use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Models\Lease;
use App\Models\Notice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLeaseRenewalRemindersJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Lease::query()
            ->where('status', LeaseStatus::Active)
            ->whereDate('ends_on', '>=', now())
            ->each(function (Lease $lease): void {
                $window = (int) ($lease->renewal_offer_days ?: config('rentos.renewal_offer_days', 60));

                if ($lease->ends_on->gt(now()->addDays($window))) {
                    return;
                }

                $exists = Notice::query()
                    ->where('lease_id', $lease->id)
                    ->where('type', NoticeType::RenewalOffer)
                    ->where('created_at', '>=', now()->subDays($window))
                    ->exists();

                if ($exists) {
                    return;
                }

                Notice::query()->create([
                    'organization_id' => $lease->organization_id,
                    'lease_id' => $lease->id,
                    'user_id' => $lease->primary_tenant_id,
                    'type' => NoticeType::RenewalOffer,
                    'effective_on' => $lease->ends_on,
                    'body' => 'Your lease ends on '.$lease->ends_on->toFormattedDateString().'. Reply to accept a renewal offer.',
                    'status' => NoticeStatus::Submitted,
                ]);

                $lease->update(['status' => LeaseStatus::PendingRenewal]);
            });
    }
}
