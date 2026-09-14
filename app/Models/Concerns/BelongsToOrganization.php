<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::creating(function ($model): void {
            if ($model->organization_id) {
                return;
            }

            $tenant = Filament::getTenant();

            if ($tenant instanceof Organization) {
                $model->organization_id = $tenant->getKey();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeForOrganization(Builder $query, Organization|int|null $organization): Builder
    {
        if ($organization === null) {
            return $query;
        }

        $id = $organization instanceof Organization ? $organization->getKey() : $organization;

        return $query->where($query->getModel()->getTable().'.organization_id', $id);
    }
}
