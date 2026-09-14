<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'name', 'jurisdiction', 'body', 'clauses', 'is_default'])]
class LeaseTemplate extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'clauses' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class, 'template_id');
    }
}
