<?php

namespace App\Filament\Owner\Resources\Inspections;

use App\Enums\InspectionStatus;
use App\Enums\InspectionType;
use App\Filament\Owner\Resources\Inspections\Pages\ManageInspections;
use App\Models\Inspection;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->relationship('property', 'name')->required()->searchable(),
            Select::make('unit_id')->relationship('unit', 'code')->searchable(),
            Select::make('lease_id')->relationship('lease', 'number')->searchable(),
            Select::make('inspector_id')->relationship('inspector', 'name')->searchable()->default(fn () => auth()->id()),
            Select::make('type')->options(InspectionType::class)->required(),
            Select::make('status')->options(InspectionStatus::class)->required(),
            DateTimePicker::make('scheduled_at'),
            Repeater::make('checklist')
                ->schema([
                    TextInput::make('item')->required(),
                    Select::make('condition')->options([
                        'good' => 'Good',
                        'fair' => 'Fair',
                        'poor' => 'Poor',
                        'na' => 'N/A',
                    ]),
                    TextInput::make('notes'),
                ])
                ->columnSpanFull()
                ->defaultItems(0),
            Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.name'),
                TextColumn::make('unit.code'),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('scheduled_at')->dateTime(),
                TextColumn::make('inspector.name'),
            ])
            ->filters([
                SelectFilter::make('type')->options(InspectionType::class),
                SelectFilter::make('status')->options(InspectionStatus::class),
            ])
            ->recordActions([
                Action::make('complete')
                    ->visible(fn (Inspection $record) => $record->status !== InspectionStatus::Completed)
                    ->action(fn (Inspection $record) => $record->update([
                        'status' => InspectionStatus::Completed,
                        'completed_at' => now(),
                    ])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInspections::route('/'),
        ];
    }
}
