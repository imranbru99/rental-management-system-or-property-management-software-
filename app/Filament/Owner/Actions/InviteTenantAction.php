<?php

namespace App\Filament\Owner\Actions;

use App\Models\Property;
use App\Models\PropertyUnit;
use App\Services\Tenants\TenantInviteService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class InviteTenantAction
{
    public static function make(?Property $property = null, string $name = 'inviteTenant'): Action
    {
        return Action::make($name)
            ->label('Add existing tenant')
            ->icon(Heroicon::OutlinedUserPlus)
            ->slideOver()
            ->modalHeading('Add an existing renter')
            ->modalDescription('Enter their name, mobile, and email. Generate a password they can use on the tenant portal.')
            ->schema([
                Select::make('unit_id')
                    ->label('Unit')
                    ->options(function (?Property $record = null) use ($property): array {
                        $building = $property ?? $record;
                        $query = PropertyUnit::query()
                            ->with('property')
                            ->orderBy('code');

                        $tenant = Filament::getTenant();

                        if ($tenant) {
                            $query->where('organization_id', $tenant->getKey());
                        }

                        if ($building) {
                            $query->where('property_id', $building->id);
                        }

                        return $query->get()
                            ->sortBy(fn (PropertyUnit $unit) => $unit->tenant_id ? 1 : 0)
                            ->mapWithKeys(function (PropertyUnit $unit) use ($building): array {
                                $label = ($unit->code ?: $unit->name).' · '.($unit->tenant_id ? 'occupied' : 'vacant');

                                if (! $building && $unit->property) {
                                    $label = $unit->property->name.' · '.$label;
                                }

                                return [$unit->id => $label];
                            })
                            ->all();
                    })
                    ->searchable()
                    ->required(),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('phone')->label('Mobile')->tel()->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('password')
                    ->label('Portal password')
                    ->required()
                    ->suffixAction(
                        Action::make('generatePassword')
                            ->icon(Heroicon::Sparkles)
                            ->label('Generate')
                            ->action(fn (Set $set) => $set('password', TenantInviteService::generatePassword()))
                    )
                    ->helperText('Share this password with the tenant. They can sign in at /tenant.'),
                Toggle::make('create_lease')->label('Create an active lease for this unit')->default(true),
                DatePicker::make('starts_on')->default(now())->visible(fn (Get $get): bool => (bool) $get('create_lease')),
                DatePicker::make('ends_on')->default(now()->addYear())->visible(fn (Get $get): bool => (bool) $get('create_lease')),
            ])
            ->fillForm(function (?Property $record = null) use ($property): array {
                $building = $property ?? $record;
                $unitId = $building
                    ? $building->units()->whereNull('tenant_id')->orderBy('code')->value('id')
                    : null;

                return [
                    'password' => TenantInviteService::generatePassword(),
                    'unit_id' => $unitId,
                ];
            })
            ->action(function (array $data, TenantInviteService $invite): void {
                $unit = PropertyUnit::query()->findOrFail($data['unit_id']);
                $result = $invite->invite($unit, $data);

                Notification::make()
                    ->title($result['user']->name.' added to unit '.($unit->code ?: $unit->name))
                    ->body('Portal login: '.$result['user']->email.' · Password: '.$result['password'])
                    ->success()
                    ->persistent()
                    ->send();
            });
    }
}
