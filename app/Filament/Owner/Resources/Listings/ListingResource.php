<?php

namespace App\Filament\Owner\Resources\Listings;

use App\Enums\ListingStatus;
use App\Filament\Owner\Resources\Listings\Pages\ManageListings;
use App\Models\Listing;
use App\Services\Ai\ListingAiService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Leasing';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->relationship('property', 'name')->required()->searchable()->live(),
            Select::make('unit_id')->relationship('unit', 'code')->searchable(),
            TextInput::make('title')->required(),
            Textarea::make('description')->columnSpanFull()->rows(6),
            Select::make('status')->options(ListingStatus::class)->required(),
            TextInput::make('rent')->numeric()->required(),
            TextInput::make('security_deposit')->numeric()->required(),
            TextInput::make('currency')->default('BDT')->required(),
            DatePicker::make('available_from'),
            Toggle::make('is_featured'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('property.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('rent')->formatStateUsing(
                    fn ($state, Listing $record) => Money::format((int) $state, $record->currency)
                ),
                IconColumn::make('is_featured')->boolean(),
            ])
            ->recordActions([
                Action::make('generate')
                    ->label('AI write')
                    ->icon(Heroicon::Sparkles)
                    ->action(function (Listing $record, ListingAiService $ai): void {
                        $ai->fillListing($record->load('property'));
                        Notification::make()->title('Listing copy generated')->success()->send();
                    }),
                Action::make('suggestRent')
                    ->label('AI rent')
                    ->icon(Heroicon::Sparkles)
                    ->action(function (Listing $record, ListingAiService $ai): void {
                        $suggested = $ai->suggestRent($record->property);
                        if ($suggested) {
                            $record->update(['rent' => $suggested]);
                            Notification::make()->title('Suggested rent applied: '.$suggested)->success()->send();

                            return;
                        }

                        Notification::make()->title('Could not suggest rent — using current asking price')->warning()->send();
                    }),
                Action::make('publish')
                    ->visible(fn (Listing $record) => $record->status !== ListingStatus::Published)
                    ->action(fn (Listing $record) => $record->update([
                        'status' => ListingStatus::Published,
                        'published_at' => now(),
                    ])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageListings::route('/'),
        ];
    }
}
