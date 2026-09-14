<?php

namespace App\Models;

use App\Enums\VendorCategory;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ImranDev\UniversalSlug\SlugOptions;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

#[Fillable([
    'organization_id', 'user_id', 'name', 'slug', 'category', 'email',
    'phone', 'rating', 'is_active', 'notes',
])]
class Vendor extends Model
{
    use BelongsToOrganization;
    use HasUniversalSlug;

    protected function casts(): array
    {
        return [
            'category' => VendorCategory::class,
            'is_active' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->uniqueScope(fn ($query, $model) => $query->where('organization_id', $model->organization_id));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
