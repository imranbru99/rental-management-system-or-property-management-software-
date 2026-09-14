<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ImranDev\UniversalSlug\SlugOptions;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

#[Fillable([
    'organization_id', 'property_id', 'floor_id', 'name', 'slug', 'code',
    'sort_order', 'status', 'bedrooms', 'bathrooms', 'balconies', 'kitchens',
    'dining', 'square_feet',
    'rent', 'security_deposit', 'amenities', 'tenant_id',
])]
class PropertyUnit extends Model
{
    use BelongsToOrganization;
    use HasUniversalSlug;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $unit): void {
            if (! $unit->property_id && $unit->floor_id) {
                $unit->property_id = PropertyFloor::query()->whereKey($unit->floor_id)->value('property_id');
            }

            $property = $unit->property_id
                ? Property::query()->find($unit->property_id)
                : null;

            $unit->name = $unit->name ?: ('Unit '.($unit->code ?: 'new'));
            $unit->status ??= PropertyStatus::Vacant;
            $unit->rent ??= $property?->default_unit_rent ?: 0;
            $unit->security_deposit ??= $property?->default_unit_deposit ?: 0;
        });
    }

    protected function casts(): array
    {
        return [
            'status' => PropertyStatus::class,
            'amenities' => 'array',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $unit): string => $unit->code ?: $unit->name)
            ->saveSlugsTo('slug')
            ->uniqueScope(fn ($query, $model) => $query->where('property_id', $model->property_id));
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(PropertyFloor::class, 'floor_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function spaces(): HasMany
    {
        return $this->hasMany(UnitSpace::class, 'unit_id')->orderBy('sort_order');
    }

    public function syncSpaceCounts(): void
    {
        $this->forceFill([
            'bedrooms' => $this->spaces()->where('type', 'room')->count(),
            'bathrooms' => $this->spaces()->where('type', 'bath')->count(),
            'balconies' => $this->spaces()->where('type', 'balcony')->count(),
            'kitchens' => $this->spaces()->where('type', 'kitchen')->count(),
            'dining' => $this->spaces()->where('type', 'dining')->count(),
        ])->save();
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'unit_id');
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class, 'unit_id');
    }
}
