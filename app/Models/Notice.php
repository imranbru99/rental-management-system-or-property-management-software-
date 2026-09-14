<?php

namespace App\Models;

use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'lease_id', 'user_id', 'type', 'effective_on', 'body', 'status'])]
class Notice extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'type' => NoticeType::class,
            'status' => NoticeStatus::class,
            'effective_on' => 'date',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
