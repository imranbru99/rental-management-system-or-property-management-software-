<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['organization_id', 'uploaded_by', 'documentable_type', 'documentable_id', 'title', 'type', 'path', 'expires_on'])]
class Document extends Model
{
    use BelongsToOrganization;

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'expires_on' => 'date',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
