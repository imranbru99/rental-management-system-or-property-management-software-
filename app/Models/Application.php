<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'reference', 'organization_id', 'listing_id', 'property_id', 'unit_id',
    'applicant_id', 'status', 'monthly_income', 'employer', 'employment_status',
    'occupants', 'has_pets', 'consent_background_check', 'references',
    'risk_score', 'risk_notes', 'reviewer_notes', 'submitted_at', 'reviewed_at', 'reviewed_by',
])]
class Application extends Model
{
    use BelongsToOrganization;
    use RecordsActivity;

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'references' => 'array',
            'has_pets' => 'boolean',
            'consent_background_check' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $application): void {
            $application->reference ??= 'APP-'.strtoupper(Str::random(8));
            $application->submitted_at ??= now();
        });
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }
}
