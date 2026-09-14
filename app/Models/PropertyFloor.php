<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'property_id', 'level', 'name', 'sort_order', 'notes'])]
class PropertyFloor extends Model
{
    use BelongsToOrganization;

    protected static function booted(): void
    {
        static::creating(function (self $floor): void {
            $floor->name = $floor->name ?: ('Floor '.($floor->level ?: ''));
            $floor->level = $floor->level ?: 1;
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(PropertyUnit::class, 'floor_id')->orderBy('sort_order');
    }
}
