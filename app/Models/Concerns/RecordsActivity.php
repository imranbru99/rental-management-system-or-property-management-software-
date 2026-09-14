<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait RecordsActivity
{
    public static function bootRecordsActivity(): void
    {
        static::created(fn (Model $model) => $model->writeAudit('created'));
        static::updated(fn (Model $model) => $model->writeAudit('updated'));
        static::deleted(fn (Model $model) => $model->writeAudit('deleted'));
    }

    protected function writeAudit(string $event): void
    {
        if (! class_exists(AuditLog::class)) {
            return;
        }

        AuditLog::query()->create([
            'organization_id' => $this->organization_id ?? null,
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'old_values' => $event === 'updated' ? $this->getOriginal() : null,
            'new_values' => $event === 'deleted' ? null : $this->getAttributes(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
