<?php

namespace App\Filament\Owner\Resources\Properties;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Filament\Owner\Actions\InviteTenantAction;
use App\Filament\Owner\Resources\Properties\Pages\BuildingLayout;
use App\Filament\Owner\Resources\Properties\Pages\BuildingOverview;
use App\Filament\Owner\Resources\Properties\Pages\CreateBuilding;
use App\Filament\Owner\Resources\Properties\Pages\ManageProperties;
use App\Models\Amenity;
use App\Models\Property;
use App\Services\Ai\ListingAiService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static string|\UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?string $navigationLabel = 'Buildings';

    protected static ?string $modelLabel = 'building';

    protected static ?string $pluralModelLabel = 'buildings';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basics')->schema([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('type')->options(PropertyType::class)->required(),
                Select::make('status')->options(PropertyStatus::class)->required(),
                TextInput::make('bedrooms')->numeric()->required()->default(1),
                TextInput::make('bathrooms')->numeric()->required()->default(1),
                TextInput::make('square_feet')->numeric(),
            ])->columns(2),
            Section::make('Location')->schema([
                TextInput::make('address_line')->required(),
                TextInput::make('city')->required(),
                TextInput::make('state'),
                TextInput::make('postal_code'),
                TextInput::make('country')->default('BD')->required(),
            ])->columns(2),
            Section::make('Pricing')->schema([
                TextInput::make('base_rent')->numeric()->required()->suffix('minor units'),
                TextInput::make('security_deposit')->numeric()->required(),
                TextInput::make('currency')->default('BDT')->required(),
                DatePicker::make('available_from'),
                Toggle::make('pet_friendly'),
                Toggle::make('furnished'),
                Toggle::make('utilities_included'),
            ])->columns(2),
            Textarea::make('description')->columnSpanFull()->rows(5),
            Textarea::make('house_rules')->columnSpanFull(),
            Select::make('amenities')
                ->multiple()
                ->options(fn () => Amenity::query()->pluck('name', 'name'))
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('city')->searchable(),
                TextColumn::make('floors_count')->label('Floors')->sortable(),
                TextColumn::make('units_count')->label('Units')->sortable(),
                TextColumn::make('occupancy')
                    ->label('Rented')
                    ->state(fn (Property $record): string => ($record->occupied_units_count ?? $record->occupiedUnits()).'/'.($record->units_count ?? $record->totalUnits())),
                TextColumn::make('units_rent_sum')
                    ->label('Total rent')
                    ->state(fn (Property $record): string => Money::format((int) ($record->units_rent_sum ?? $record->totalMonthlyRent()), $record->currency)),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options(PropertyStatus::class),
                SelectFilter::make('type')->options(PropertyType::class),
            ])
            ->recordUrl(fn (Property $record): string => static::getUrl('overview', ['record' => $record]))
            ->recordActions([
                Action::make('overview')
                    ->label('Overview')
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->url(fn (Property $record): string => static::getUrl('overview', ['record' => $record])),
                Action::make('layout')
                    ->label('Layout')
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->url(fn (Property $record): string => static::getUrl('layout', ['record' => $record])),
                InviteTenantAction::make(),
                EditAction::make(),
                Action::make('aiCopy')
                    ->label('AI listing copy')
                    ->icon(Heroicon::Sparkles)
                    ->action(function (Property $record, ListingAiService $ai): void {
                        $copy = $ai->generateCopy($record);
                        $record->update(['description' => $copy['description']]);
                        Notification::make()->title('AI description saved')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'floors',
                'units',
                'units as occupied_units_count' => fn (Builder $query) => $query->where('status', PropertyStatus::Occupied),
            ])
            ->withSum('units as units_rent_sum', 'rent');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProperties::route('/'),
            'create' => CreateBuilding::route('/create'),
            'overview' => BuildingOverview::route('/{record}/overview'),
            'layout' => BuildingLayout::route('/{record}/layout'),
        ];
    }
}
