<?php

namespace App\Filament\Tenant\Resources\Reviews;

use App\Filament\Tenant\Resources\Reviews\Pages\ManageReviews;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|\UnitEnum|null $navigationGroup = 'Home';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {
                $query->where('reviewer_id', auth()->id())
                    ->orWhere(function (Builder $inner): void {
                        $inner->where('is_revealed', true)
                            ->where('reviewee_type', User::class)
                            ->where('reviewee_id', auth()->id());
                    });
            });
    }

    public static function form(Schema $schema): Schema
    {
        $propertyIds = auth()->user()?->leases()->pluck('leases.property_id') ?? collect();

        return $schema->components([
            Select::make('reviewee_id')
                ->label('Building')
                ->options(fn () => Property::query()->whereIn('id', $propertyIds)->pluck('name', 'id'))
                ->required(),
            TextInput::make('rating')->numeric()->minValue(1)->maxValue(5)->required(),
            Textarea::make('body'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reviewer.name'),
                TextColumn::make('rating'),
                TextColumn::make('body')->limit(40),
                IconColumn::make('is_revealed')->boolean(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReviews::route('/'),
        ];
    }
}
