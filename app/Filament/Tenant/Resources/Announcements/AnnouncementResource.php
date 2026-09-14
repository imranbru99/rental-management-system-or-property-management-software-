<?php

namespace App\Filament\Tenant\Resources\Announcements;

use App\Filament\Tenant\Resources\Announcements\Pages\ManageAnnouncements;
use App\Models\Announcement;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Home';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $leaseQuery = $user?->leases();
        $orgIds = $leaseQuery?->pluck('leases.organization_id') ?? collect();
        $propertyIds = $user?->leases()->pluck('leases.property_id') ?? collect();

        return parent::getEloquentQuery()
            ->whereNotNull('published_at')
            ->whereIn('organization_id', $orgIds)
            ->where(function (Builder $query) use ($propertyIds): void {
                $query->whereNull('property_id')
                    ->orWhereIn('property_id', $propertyIds);
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('property.name')->placeholder('Whole portfolio'),
                TextColumn::make('published_at')->since(),
                TextColumn::make('body')->limit(80),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAnnouncements::route('/'),
        ];
    }
}
