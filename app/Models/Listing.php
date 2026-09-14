<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ImranDev\UniversalSlug\SlugOptions;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

#[Fillable([
    'organization_id', 'property_id', 'unit_id', 'title', 'slug', 'description',
    'status', 'rent', 'security_deposit', 'currency', 'available_from',
    'is_featured', 'moderation_notes', 'ai_generated', 'published_at', 'unlisted_at',
])]
class Listing extends Model
{
    use BelongsToOrganization;
    use HasUniversalSlug;
    use RecordsActivity;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ListingStatus::class,
            'available_from' => 'date',
            'is_featured' => 'boolean',
            'ai_generated' => 'boolean',
            'published_at' => 'datetime',
            'unlisted_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->useAsRouteKey()
            ->withWordLimit(10)
            ->uniqueScope(fn ($query, $model) => $query->where('organization_id', $model->organization_id));
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function viewings(): HasMany
    {
        return $this->hasMany(Viewing::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Published);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
