<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ImranDev\UniversalSlug\SlugOptions;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

#[Fillable([
    'organization_id', 'owner_user_id', 'name', 'slug', 'type', 'status',
    'address_line', 'address_line_2', 'city', 'state', 'postal_code', 'country',
    'latitude', 'longitude', 'bedrooms', 'bathrooms', 'square_feet', 'year_built',
    'base_rent', 'security_deposit', 'currency', 'utilities_included', 'pet_friendly',
    'furnished', 'amenities', 'description', 'house_rules', 'cover_photo_path',
    'video_url', 'floor_plan_path', 'photos', 'pricing_rules', 'available_from',
    'is_featured', 'featured_until', 'floor_count', 'units_per_floor',
    'default_rooms', 'default_baths', 'default_balconies', 'default_kitchens',
    'default_dining', 'default_unit_rent', 'default_unit_deposit',
])]
class Property extends Model
{
    use BelongsToOrganization;
    use HasUniversalSlug;
    use RecordsActivity;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $property): void {
            $property->owner_user_id ??= auth()->id();
        });
    }

    protected function casts(): array
    {
        return [
            'type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'amenities' => 'array',
            'photos' => 'array',
            'pricing_rules' => 'array',
            'utilities_included' => 'boolean',
            'pet_friendly' => 'boolean',
            'furnished' => 'boolean',
            'is_featured' => 'boolean',
            'available_from' => 'date',
            'featured_until' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->useAsRouteKey()
            ->uniqueScope(fn ($query, $model) => $query->where('organization_id', $model->organization_id));
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function floors(): HasMany
    {
        return $this->hasMany(PropertyFloor::class)->orderBy('sort_order');
    }

    public function units(): HasMany
    {
        return $this->hasMany(PropertyUnit::class)->orderBy('sort_order');
    }

    public function totalUnits(): int
    {
        return $this->units()->count();
    }

    public function occupiedUnits(): int
    {
        return $this->units()->where('status', PropertyStatus::Occupied)->count();
    }

    public function totalMonthlyRent(): int
    {
        return (int) $this->units()->sum('rent');
    }

    public function collectedMonthlyRent(): int
    {
        return (int) $this->units()->where('status', PropertyStatus::Occupied)->sum('rent');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->address_line, $this->city, $this->state, $this->postal_code, $this->country])
            ->filter()
            ->implode(', ');
    }
}
