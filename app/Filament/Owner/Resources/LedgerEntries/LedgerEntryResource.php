<?php

namespace App\Filament\Owner\Resources\LedgerEntries;

use App\Filament\Owner\Resources\LedgerEntries\Pages\ManageLedgerEntries;
use App\Models\LedgerEntry;
use App\Support\Money;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LedgerEntryResource extends Resource
{
    protected static ?string $model = LedgerEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Ledger';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('posted_at')->dateTime()->sortable(),
                TextColumn::make('account'),
                TextColumn::make('entry_type')->badge(),
                TextColumn::make('amount')->formatStateUsing(
                    fn ($state, LedgerEntry $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('property.name'),
                TextColumn::make('memo')->limit(40),
            ])
            ->filters([
                SelectFilter::make('entry_type')->options([
                    'debit' => 'Debit',
                    'credit' => 'Credit',
                ]),
            ])
            ->defaultSort('posted_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLedgerEntries::route('/'),
        ];
    }
}
