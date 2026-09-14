<?php

namespace App\Filament\Tenant\Resources\MaintenanceRequests;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Filament\Tenant\Resources\MaintenanceRequests\Pages\ManageMaintenanceRequests;
use App\Models\MaintenanceRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|\UnitEnum|null $navigationGroup = 'Home';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->relationship('property', 'name')->required()->searchable(),
            TextInput::make('title')->required(),
            Textarea::make('description')->required(),
            Select::make('priority')->options(MaintenancePriority::class)->default(MaintenancePriority::Routine)->required(),
            TextInput::make('category')->default('general'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference'),
                TextColumn::make('title'),
                TextColumn::make('priority')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since(),
            ])
            ->recordActions([
                Action::make('comment')
                    ->form([Textarea::make('body')->required()])
                    ->action(function (MaintenanceRequest $record, array $data): void {
                        $record->comments()->create([
                            'user_id' => auth()->id(),
                            'body' => $data['body'],
                        ]);
                        Notification::make()->title('Comment added')->success()->send();
                    }),
                Action::make('rate')
                    ->visible(fn (MaintenanceRequest $record) => $record->status === MaintenanceStatus::Resolved)
                    ->form([
                        TextInput::make('rating')->numeric()->minValue(1)->maxValue(5)->required(),
                        Textarea::make('rating_comment'),
                    ])
                    ->action(function (MaintenanceRequest $record, array $data): void {
                        $record->update([
                            'rating' => $data['rating'],
                            'rating_comment' => $data['rating_comment'] ?? null,
                            'status' => MaintenanceStatus::Rated,
                        ]);
                        Notification::make()->title('Thanks for the rating')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMaintenanceRequests::route('/'),
        ];
    }
}
