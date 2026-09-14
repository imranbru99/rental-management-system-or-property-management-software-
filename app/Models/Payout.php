<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'organization_id', 'gross_amount', 'platform_fee', 'net_amount', 'currency',
    'status', 'destination', 'scheduled_for', 'paid_at',
])]
class Payout extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'status' => PayoutStatus::class,
            'scheduled_for' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}
