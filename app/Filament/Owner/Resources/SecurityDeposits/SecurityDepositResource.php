<?php

namespace App\Filament\Owner\Resources\SecurityDeposits;

use App\Enums\DepositStatus;
use App\Filament\Owner\Resources\SecurityDeposits\Pages\ManageSecurityDeposits;
use App\Models\SecurityDeposit;
use App\Services\Deposits\DepositService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SecurityDepositResource extends Resource
{
    protected static ?string $model = SecurityDeposit::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Deposits';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lease.number'),
                TextColumn::make('lease.primaryTenant.name')->label('Tenant'),
                TextColumn::make('amount')->formatStateUsing(
                    fn ($state, SecurityDeposit $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('held_amount')->label('Held')->formatStateUsing(
                    fn ($state, SecurityDeposit $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options(DepositStatus::class),
            ])
            ->recordActions([
                Action::make('deduct')
                    ->visible(fn (SecurityDeposit $record) => $record->held_amount > 0)
                    ->form([
                        TextInput::make('amount')->numeric()->required(),
                        Textarea::make('reason')->required(),
                    ])
                    ->action(function (SecurityDeposit $record, array $data, DepositService $deposits): void {
                        $deposits->deduct($record, (int) $data['amount'], $data['reason']);
                        Notification::make()->title('Deduction recorded')->success()->send();
                    }),
                Action::make('refund')
                    ->visible(fn (SecurityDeposit $record) => $record->held_amount > 0)
                    ->form([
                        TextInput::make('amount')->numeric()->required(),
                    ])
                    ->action(function (SecurityDeposit $record, array $data, DepositService $deposits): void {
                        $deposits->refund($record, (int) $data['amount']);
                        Notification::make()->title('Refund recorded')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSecurityDeposits::route('/'),
        ];
    }
}
