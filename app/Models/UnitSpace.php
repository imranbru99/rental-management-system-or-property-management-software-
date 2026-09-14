<?php

namespace App\Models;

use App\Enums\UnitSpaceType;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'unit_id', 'type', 'name', 'sort_order'])]
class UnitSpace extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'type' => UnitSpaceType::class,
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }
}
