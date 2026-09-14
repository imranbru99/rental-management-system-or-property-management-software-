<?php

namespace App\Filament\Tenant\Resources\Applications;

use App\Enums\ApplicationStatus;
use App\Filament\Tenant\Resources\Applications\Pages\ManageApplications;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Discover';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('applicant_id', auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference'),
                TextColumn::make('listing.title')->limit(28),
                TextColumn::make('status')->badge(),
                TextColumn::make('risk_score'),
                TextColumn::make('submitted_at')->since(),
            ])
            ->recordActions([
                Action::make('withdraw')
                    ->visible(fn (Application $record) => in_array($record->status, [ApplicationStatus::Submitted, ApplicationStatus::Screening], true))
                    ->action(fn (Application $record) => $record->update(['status' => ApplicationStatus::Withdrawn])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageApplications::route('/'),
        ];
    }
}
