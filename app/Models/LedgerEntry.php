<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'organization_id', 'property_id', 'lease_id', 'reference_type', 'reference_id',
    'account', 'entry_type', 'amount', 'currency', 'memo', 'posted_at',
])]
class LedgerEntry extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
