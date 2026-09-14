<?php

namespace App\Models;

use App\Enums\ViewingStatus;
use App\Enums\ViewingType;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id', 'listing_id', 'property_id', 'applicant_id', 'host_id',
    'type', 'status', 'scheduled_at', 'meeting_url', 'notes',
])]
class Viewing extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'type' => ViewingType::class,
            'status' => ViewingStatus::class,
            'scheduled_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }
}
