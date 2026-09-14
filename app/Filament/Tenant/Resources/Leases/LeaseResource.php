<?php

namespace App\Filament\Tenant\Resources\Leases;

use App\Enums\LeaseStatus;
use App\Filament\Tenant\Resources\Leases\Pages\ManageLeases;
use App\Models\Lease;
use App\Services\Documents\LeasePdfService;
use App\Services\Leasing\LeaseSigningService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaseResource extends Resource
{
    protected static ?string $model = Lease::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Home';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {
                $query->where('primary_tenant_id', auth()->id())
                    ->orWhereHas('tenants', fn (Builder $tenants) => $tenants->where('users.id', auth()->id()));
            });
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number'),
                TextColumn::make('property.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('rent')->formatStateUsing(
                    fn ($state, Lease $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('ends_on')->date(),
            ])
            ->recordActions([
                Action::make('sign')
                    ->visible(fn (Lease $record) => in_array($record->status, [LeaseStatus::Sent, LeaseStatus::PartiallySigned, LeaseStatus::Draft], true)
                        && ! $record->signatures()->where('user_id', auth()->id())->exists())
                    ->action(function (Lease $record, LeaseSigningService $signing): void {
                        $signing->sign($record, auth()->user(), 'tenant');
                        Notification::make()->title('You signed this lease')->success()->send();
                    }),
                Action::make('pdf')->action(fn (Lease $record, LeasePdfService $pdf) => $pdf->download($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLeases::route('/'),
        ];
    }
}
