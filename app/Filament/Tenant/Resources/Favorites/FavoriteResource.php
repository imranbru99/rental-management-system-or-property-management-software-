<?php

namespace App\Filament\Tenant\Resources\Favorites;

use App\Filament\Tenant\Resources\Favorites\Pages\ManageFavorites;
use App\Models\Favorite;
use App\Support\Money;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FavoriteResource extends Resource
{
    protected static ?string $model = Favorite::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|\UnitEnum|null $navigationGroup = 'Discover';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id())->with('listing.property');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('listing.title')->searchable(),
                TextColumn::make('listing.property.city')->label('City'),
                TextColumn::make('listing.rent')->formatStateUsing(
                    fn ($state, Favorite $record) => Money::format((int) ($record->listing?->rent ?? 0), $record->listing?->currency ?? 'BDT')
                ),
                TextColumn::make('created_at')->since()->label('Saved'),
            ])
            ->recordActions([
                DeleteAction::make()->label('Remove'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFavorites::route('/'),
        ];
    }
}
