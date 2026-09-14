<?php

namespace App\Filament\Owner\Resources\CalendarBlocks;

use App\Filament\Owner\Resources\CalendarBlocks\Pages\ManageCalendarBlocks;
use App\Models\CalendarBlock;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CalendarBlockResource extends Resource
{
    protected static ?string $model = CalendarBlock::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Portfolio';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Availability';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->relationship('property', 'name')->required()->searchable(),
            Select::make('unit_id')->relationship('unit', 'code')->searchable(),
            DatePicker::make('starts_on')->required(),
            DatePicker::make('ends_on')->required(),
            TextInput::make('reason'),
            Select::make('source')->options([
                'manual' => 'Manual',
                'ical' => 'iCal',
                'booking' => 'Booking',
            ])->default('manual')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.name'),
                TextColumn::make('unit.code'),
                TextColumn::make('starts_on')->date(),
                TextColumn::make('ends_on')->date(),
                TextColumn::make('reason'),
                TextColumn::make('source')->badge(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCalendarBlocks::route('/'),
        ];
    }
}
