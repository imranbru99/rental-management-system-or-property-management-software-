<?php

namespace App\Services\Leasing;

use App\Enums\LeaseStatus;
use App\Enums\UserRole;
use App\Models\Lease;
use App\Models\LeaseSignature;
use App\Models\User;

class LeaseSigningService
{
    public function send(Lease $lease): Lease
    {
        $lease->update([
            'status' => LeaseStatus::Sent,
            'sent_at' => now(),
        ]);

        return $lease->refresh();
    }

    public function sign(Lease $lease, User $user, ?string $role = null): LeaseSignature
    {
        $signature = $lease->signatures()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'role' => $role ?? $this->roleFor($user),
                'ip_address' => request()->ip(),
                'signed_at' => now(),
            ],
        );

        $this->refreshStatus($lease);

        return $signature;
    }

    public function refreshStatus(Lease $lease): void
    {
        $lease->load('signatures');

        if ($lease->status === LeaseStatus::Active) {
            return;
        }

        $hasOwner = $lease->signatures->contains(fn (LeaseSignature $signature) => in_array($signature->role, ['owner', 'org_admin', 'assistant'], true));
        $hasTenant = $lease->signatures->contains(fn (LeaseSignature $signature) => $signature->role === 'tenant');

        if ($hasOwner && $hasTenant) {
            $lease->update(['status' => LeaseStatus::PartiallySigned]);

            return;
        }

        if ($lease->signatures->isNotEmpty()) {
            $lease->update(['status' => LeaseStatus::PartiallySigned]);
        }
    }

    protected function roleFor(User $user): string
    {
        return match ($user->role) {
            UserRole::Owner, UserRole::OrgAdmin => 'owner',
            UserRole::Assistant, UserRole::Agent => 'assistant',
            default => 'tenant',
        };
    }
}
