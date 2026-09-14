<?php

namespace App\Filament\Owner\Resources\Payouts;

use App\Enums\PayoutStatus;
use App\Filament\Owner\Resources\Payouts\Pages\ManagePayouts;
use App\Models\Payout;
use App\Services\Billing\PayoutService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayoutResource extends Resource
{
    protected static ?string $model = Payout::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 40;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('gross_amount')->formatStateUsing(
                    fn ($state, Payout $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('platform_fee')->formatStateUsing(
                    fn ($state, Payout $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('net_amount')->formatStateUsing(
                    fn ($state, Payout $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('destination'),
                TextColumn::make('status')->badge(),
                TextColumn::make('scheduled_for')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')->options(PayoutStatus::class),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->visible(fn (Payout $record) => $record->status !== PayoutStatus::Paid)
                    ->action(function (Payout $record, PayoutService $payouts): void {
                        $payouts->markPaid($record);
                        Notification::make()->title('Payout marked paid')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePayouts::route('/'),
        ];
    }
}
