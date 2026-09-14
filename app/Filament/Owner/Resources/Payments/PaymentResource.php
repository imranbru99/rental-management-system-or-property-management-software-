<?php

namespace App\Filament\Owner\Resources\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Owner\Resources\Payments\Pages\ManagePayments;
use App\Models\Payment;
use App\Support\Money;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 20;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable(),
                TextColumn::make('invoice.number'),
                TextColumn::make('payer.name'),
                TextColumn::make('amount')->formatStateUsing(
                    fn ($state, Payment $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('method')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('paid_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('method')->options(PaymentMethod::class),
                SelectFilter::make('status')->options(PaymentStatus::class),
            ])
            ->defaultSort('paid_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePayments::route('/'),
        ];
    }
}
