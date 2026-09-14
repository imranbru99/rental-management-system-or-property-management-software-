<?php

namespace App\Filament\Tenant\Resources\Viewings;

use App\Enums\ViewingStatus;
use App\Enums\ViewingType;
use App\Filament\Tenant\Resources\Viewings\Pages\ManageViewings;
use App\Models\Viewing;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewingResource extends Resource
{
    protected static ?string $model = Viewing::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Discover';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('applicant_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('listing_id')->relationship('listing', 'title')->required()->searchable(),
            Select::make('type')->options(ViewingType::class)->default(ViewingType::InPerson)->required(),
            DateTimePicker::make('scheduled_at')->required(),
            Textarea::make('notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('listing.title')->limit(32),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('scheduled_at')->dateTime(),
            ])
            ->recordActions([
                Action::make('cancel')
                    ->visible(fn (Viewing $record) => in_array($record->status, [ViewingStatus::Requested, ViewingStatus::Confirmed], true))
                    ->action(fn (Viewing $record) => $record->update(['status' => ViewingStatus::Cancelled])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageViewings::route('/'),
        ];
    }
}
