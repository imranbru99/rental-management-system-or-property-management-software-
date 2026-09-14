<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ImranDev\UniversalSlug\Attributes\Slug;
use ImranDev\UniversalSlug\Traits\HasUniversalSlug;

#[Fillable(['name', 'slug', 'description', 'price_monthly', 'price_yearly', 'currency', 'features', 'limits', 'is_active', 'sort_order'])]
#[Slug(from: 'name', to: 'slug', reserved: true)]
class Plan extends Model
{
    use HasUniversalSlug;

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'limits' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }
}
