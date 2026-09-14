<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id', 'property_id', 'vendor_id', 'category', 'title',
    'amount', 'currency', 'spent_on', 'receipt_path', 'notes',
])]
class Expense extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'spent_on' => 'date',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
