<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['security_deposit_id', 'amount', 'reason'])]
class DepositDeduction extends Model
{
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(SecurityDeposit::class, 'security_deposit_id');
    }
}
