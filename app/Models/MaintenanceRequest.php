<?php

namespace App\Models;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'reference', 'organization_id', 'property_id', 'unit_id', 'lease_id',
    'tenant_id', 'assigned_to', 'vendor_id', 'title', 'description', 'category',
    'priority', 'status', 'estimated_cost', 'actual_cost', 'photos', 'sla_due_at',
    'scheduled_at', 'resolved_at', 'rating', 'rating_comment',
])]
class MaintenanceRequest extends Model
{
    use BelongsToOrganization;
    use RecordsActivity;

    protected function casts(): array
    {
        return [
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceStatus::class,
            'photos' => 'array',
            'sla_due_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $request): void {
            $request->reference ??= 'MNT-'.strtoupper(Str::random(8));

            if ($request->priority instanceof MaintenancePriority && ! $request->sla_due_at) {
                $request->sla_due_at = now()->addHours($request->priority->slaHours());
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MaintenanceComment::class);
    }
}
