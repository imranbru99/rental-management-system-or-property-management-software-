<?php

namespace App\Filament\Tenant\Resources\Referrals;

use App\Filament\Tenant\Resources\Referrals\Pages\ManageReferrals;
use App\Models\Referral;
use App\Support\Money;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|\UnitEnum|null $navigationGroup = 'Discover';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('referrer_id', auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->copyable(),
                TextColumn::make('referee.name')->placeholder('Not claimed'),
                TextColumn::make('credit_amount')->formatStateUsing(
                    fn ($state) => Money::format((int) $state)
                ),
                TextColumn::make('status')->badge(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReferrals::route('/'),
        ];
    }
}
