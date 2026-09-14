<?php

namespace App\Models;

use App\Enums\InspectionStatus;
use App\Enums\InspectionType;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id', 'property_id', 'unit_id', 'lease_id', 'inspector_id',
    'type', 'status', 'scheduled_at', 'completed_at', 'checklist', 'photos',
    'notes', 'pdf_path',
])]
class Inspection extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'type' => InspectionType::class,
            'status' => InspectionStatus::class,
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'checklist' => 'array',
            'photos' => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
