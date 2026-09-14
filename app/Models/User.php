<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'phone', 'avatar_path', 'password', 'role', 'status',
    'locale', 'timezone', 'date_of_birth', 'national_id', 'last_login_at',
    'current_organization_id', 'email_verified_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, HasDefaultTenant, HasName, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_user_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->using(Membership::class)
            ->withPivot(['role', 'permissions', 'property_ids', 'is_point_of_contact', 'status'])
            ->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'applicant_id');
    }

    public function leases(): BelongsToMany
    {
        return $this->belongsToMany(Lease::class, 'lease_tenants')
            ->withPivot(['is_primary', 'rent_share', 'role'])
            ->withTimestamps();
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'favorites')->withTimestamps();
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function viewings(): HasMany
    {
        return $this->hasMany(Viewing::class, 'applicant_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->status !== UserStatus::Active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->isSuperAdmin(),
            'owner' => $this->role->canAccessOwnerPanel() && $this->organizations()->exists(),
            'tenant' => $this->role->canAccessTenantPanel(),
            default => false,
        };
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->organizations()->where('organizations.status', '!=', 'cancelled')->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->isSuperAdmin() || $this->organizations()->whereKey($tenant)->exists();
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->currentOrganization ?? $this->organizations()->first();
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_path ? asset('storage/'.$this->avatar_path) : null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function membershipFor(?Organization $organization): ?Membership
    {
        if (! $organization) {
            return null;
        }

        return Membership::query()
            ->where('organization_id', $organization->getKey())
            ->where('user_id', $this->getKey())
            ->first();
    }

    public function hasPermission(string $permission, ?Organization $organization = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $membership = $this->membershipFor($organization ?? $this->currentOrganization);

        if (! $membership) {
            return false;
        }

        if (in_array($membership->role, [UserRole::OrgAdmin, UserRole::Owner], true)) {
            return true;
        }

        return in_array($permission, $membership->permissions ?? [], true);
    }
}
