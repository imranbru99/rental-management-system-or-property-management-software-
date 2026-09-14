<?php

namespace App\Filament\Resources\Listings;

use App\Enums\ListingStatus;
use App\Filament\Resources\Listings\Pages\ManageListingModeration;
use App\Models\Listing;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ListingModerationResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?string $navigationLabel = 'Listing moderation';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')->options(ListingStatus::class)->required(),
            Textarea::make('moderation_notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('organization.name'),
                TextColumn::make('property.city'),
                TextColumn::make('status')->badge(),
                TextColumn::make('published_at')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ListingStatus::class),
            ])
            ->recordActions([
                Action::make('approve')
                    ->color('success')
                    ->visible(fn (Listing $record) => $record->status === ListingStatus::PendingReview)
                    ->action(fn (Listing $record) => $record->update([
                        'status' => ListingStatus::Published,
                        'published_at' => now(),
                    ])),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageListingModeration::route('/'),
        ];
    }
}
