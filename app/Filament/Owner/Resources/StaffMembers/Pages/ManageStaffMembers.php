<?php

namespace App\Filament\Owner\Resources\StaffMembers\Pages;

use App\Enums\AssistantPermission;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Owner\Resources\StaffMembers\StaffMemberResource;
use App\Models\Membership;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManageStaffMembers extends ManageRecords
{
    protected static string $resource = StaffMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('email')->email()->required(),
                    TextInput::make('phone'),
                    Select::make('role')
                        ->options([
                            UserRole::Assistant->value => 'Property manager',
                            UserRole::Agent->value => 'Agent',
                        ])
                        ->default(UserRole::Assistant->value)
                        ->required(),
                ])
                ->using(function (array $data): Membership {
                    $role = UserRole::from($data['role']);

                    $user = User::query()->firstOrCreate(
                        ['email' => $data['email']],
                        [
                            'name' => $data['name'],
                            'phone' => $data['phone'] ?? null,
                            'password' => Hash::make(Str::password(12)),
                            'role' => $role,
                            'status' => UserStatus::Active,
                        ],
                    );

                    return Membership::query()->updateOrCreate(
                        [
                            'organization_id' => Filament::getTenant()->getKey(),
                            'user_id' => $user->id,
                        ],
                        [
                            'role' => $role,
                            'permissions' => AssistantPermission::defaultFor($role),
                            'status' => UserStatus::Active,
                            'invited_at' => now(),
                            'accepted_at' => now(),
                        ],
                    );
                }),
        ];
    }
}
