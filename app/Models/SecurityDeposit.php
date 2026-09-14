<?php

namespace App\Models;

use App\Enums\DepositStatus;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'lease_id', 'amount', 'held_amount', 'currency', 'status', 'refunded_at'])]
class SecurityDeposit extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'status' => DepositStatus::class,
            'refunded_at' => 'datetime',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(DepositDeduction::class);
    }
}
