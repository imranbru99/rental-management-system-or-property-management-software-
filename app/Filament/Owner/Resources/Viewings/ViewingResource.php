<?php

namespace App\Filament\Owner\Resources\Viewings;

use App\Enums\ViewingStatus;
use App\Enums\ViewingType;
use App\Filament\Owner\Resources\Viewings\Pages\ManageViewings;
use App\Models\Viewing;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ViewingResource extends Resource
{
    protected static ?string $model = Viewing::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Leasing';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('listing_id')->relationship('listing', 'title')->required()->searchable(),
            Select::make('property_id')->relationship('property', 'name')->required()->searchable(),
            Select::make('applicant_id')->relationship('applicant', 'name')->required()->searchable(),
            Select::make('host_id')->relationship('host', 'name')->searchable(),
            Select::make('type')->options(ViewingType::class)->required(),
            Select::make('status')->options(ViewingStatus::class)->required(),
            DateTimePicker::make('scheduled_at')->required(),
            TextInput::make('meeting_url')->url(),
            Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('listing.title')->limit(28)->searchable(),
                TextColumn::make('applicant.name'),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('scheduled_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ViewingStatus::class),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->visible(fn (Viewing $record) => $record->status === ViewingStatus::Requested)
                    ->action(fn (Viewing $record) => $record->update(['status' => ViewingStatus::Confirmed])),
                Action::make('complete')
                    ->visible(fn (Viewing $record) => $record->status === ViewingStatus::Confirmed)
                    ->action(fn (Viewing $record) => $record->update(['status' => ViewingStatus::Completed])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageViewings::route('/'),
        ];
    }
}
