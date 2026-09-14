<?php

namespace App\Models;

use App\Enums\LeaseStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'number', 'organization_id', 'property_id', 'unit_id', 'application_id',
    'template_id', 'primary_tenant_id', 'status', 'starts_on', 'ends_on', 'rent',
    'security_deposit', 'currency', 'due_day', 'late_fee_grace_days',
    'late_fee_percent', 'notice_days', 'renewal_offer_days', 'auto_renew',
    'escalation_percent', 'terms', 'pdf_path', 'sent_at', 'activated_at', 'ended_at',
])]
class Lease extends Model
{
    use BelongsToOrganization;
    use RecordsActivity;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => LeaseStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'auto_renew' => 'boolean',
            'sent_at' => 'datetime',
            'activated_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $lease): void {
            $lease->number ??= 'LSE-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
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

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LeaseTemplate::class, 'template_id');
    }

    public function primaryTenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_tenant_id');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lease_tenants')
            ->withPivot(['is_primary', 'rent_share', 'role'])
            ->withTimestamps();
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(LeaseSignature::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function deposit(): HasOne
    {
        return $this->hasOne(SecurityDeposit::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'property_id', 'property_id');
    }
}
