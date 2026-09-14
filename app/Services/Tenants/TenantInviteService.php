<?php

namespace App\Services\Tenants;

use App\Enums\LeaseStatus;
use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Lease;
use App\Models\PropertyUnit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantInviteService
{
    /**
     * @return array{user: User, password: string, lease: ?Lease}
     */
    public function invite(PropertyUnit $unit, array $data): array
    {
        $password = $data['password'] ?? self::generatePassword();

        return DB::transaction(function () use ($unit, $data, $password): array {
            $user = User::query()->firstOrNew([
                'email' => $data['email'],
            ]);

            $user->fill([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? $user->phone,
                'role' => UserRole::Tenant,
                'status' => UserStatus::Active,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            if (! $user->exists || filled($data['password'] ?? null) || ! $user->password) {
                $user->password = $password;
            } else {
                $password = '(existing account — password unchanged)';
            }

            $user->save();

            $organization = $unit->organization;

            if ($organization && ! $user->organizations()->whereKey($organization->id)->exists()) {
                $organization->members()->attach($user->id, [
                    'role' => UserRole::Tenant->value,
                    'permissions' => [],
                    'status' => UserStatus::Active->value,
                    'accepted_at' => now(),
                ]);
            }

            if ($organization && ! $user->current_organization_id) {
                $user->forceFill(['current_organization_id' => $organization->id])->save();
            }

            $unit->forceFill([
                'tenant_id' => $user->id,
                'status' => PropertyStatus::Occupied,
            ])->save();

            $lease = null;

            if ($data['create_lease'] ?? true) {
                $lease = Lease::query()->create([
                    'organization_id' => $unit->organization_id,
                    'property_id' => $unit->property_id,
                    'unit_id' => $unit->id,
                    'primary_tenant_id' => $user->id,
                    'status' => LeaseStatus::Active,
                    'starts_on' => $data['starts_on'] ?? now()->toDateString(),
                    'ends_on' => $data['ends_on'] ?? now()->addYear()->toDateString(),
                    'rent' => $unit->rent,
                    'security_deposit' => $unit->security_deposit,
                    'currency' => $unit->property?->currency ?? 'BDT',
                    'activated_at' => now(),
                    'terms' => 'Existing tenant added by owner.',
                ]);

                $lease->tenants()->sync([
                    $user->id => ['is_primary' => true, 'role' => 'tenant'],
                ]);
            }

            return [
                'user' => $user,
                'password' => $password,
                'lease' => $lease,
            ];
        });
    }

    public static function generatePassword(): string
    {
        return Str::lower(Str::random(4)).'-'.random_int(1000, 9999);
    }
}
