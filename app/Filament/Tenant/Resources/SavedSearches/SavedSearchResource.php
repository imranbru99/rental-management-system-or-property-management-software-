<?php

namespace App\Filament\Tenant\Resources\SavedSearches;

use App\Filament\Tenant\Resources\SavedSearches\Pages\ManageSavedSearches;
use App\Models\SavedSearch;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SavedSearchResource extends Resource
{
    protected static ?string $model = SavedSearch::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|\UnitEnum|null $navigationGroup = 'Discover';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            TextInput::make('criteria.q')->label('Keywords / city'),
            TextInput::make('criteria.min_bedrooms')->numeric()->label('Min bedrooms'),
            TextInput::make('criteria.max_rent')->numeric()->label('Max rent (minor units)'),
            Toggle::make('criteria.pets')->label('Pet-friendly'),
            Toggle::make('criteria.furnished'),
            Select::make('alert_frequency')->options([
                'off' => 'Off',
                'daily' => 'Daily',
                'weekly' => 'Weekly',
            ])->default('daily')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('alert_frequency')->badge(),
                TextColumn::make('last_alerted_at')->since()->placeholder('Never'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSavedSearches::route('/'),
        ];
    }
}
