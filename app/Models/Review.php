<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'organization_id', 'reviewer_id', 'reviewee_type', 'reviewee_id',
    'rating', 'body', 'is_revealed', 'revealed_at',
])]
class Review extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'is_revealed' => 'boolean',
            'revealed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): MorphTo
    {
        return $this->morphTo();
    }
}
