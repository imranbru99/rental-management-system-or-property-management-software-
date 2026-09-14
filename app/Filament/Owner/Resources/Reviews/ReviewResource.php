<?php

namespace App\Filament\Owner\Resources\Reviews;

use App\Filament\Owner\Resources\Reviews\Pages\ManageReviews;
use App\Models\Review;
use App\Models\User;
use App\Services\Reviews\ReviewService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('reviewee_id')
                ->label('Tenant')
                ->options(fn () => User::query()->where('role', 'tenant')->pluck('name', 'id'))
                ->searchable()
                ->required(),
            TextInput::make('rating')->numeric()->minValue(1)->maxValue(5)->required(),
            Textarea::make('body')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reviewer.name'),
                TextColumn::make('reviewee_type')->label('About')->formatStateUsing(
                    fn (?string $state) => class_basename((string) $state)
                ),
                TextColumn::make('rating'),
                IconColumn::make('is_revealed')->boolean(),
                TextColumn::make('created_at')->since(),
            ])
            ->recordActions([
                Action::make('reveal')
                    ->visible(fn (Review $record) => ! $record->is_revealed)
                    ->action(fn (Review $record, ReviewService $reviews) => $reviews->reveal($record)),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReviews::route('/'),
        ];
    }
}
