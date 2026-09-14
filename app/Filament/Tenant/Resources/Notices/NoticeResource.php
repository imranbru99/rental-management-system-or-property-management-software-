<?php

namespace App\Filament\Tenant\Resources\Notices;

use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Filament\Tenant\Resources\Notices\Pages\ManageNotices;
use App\Models\Notice;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NoticeResource extends Resource
{
    protected static ?string $model = Notice::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|\UnitEnum|null $navigationGroup = 'Home';

    public static function getEloquentQuery(): Builder
    {
        $leaseIds = auth()->user()?->leases()->pluck('leases.id') ?? collect();

        return parent::getEloquentQuery()
            ->where(function (Builder $query) use ($leaseIds): void {
                $query->where('user_id', auth()->id())
                    ->orWhereIn('lease_id', $leaseIds);
            });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('lease_id')
                ->relationship(
                    'lease',
                    'number',
                    fn (Builder $query) => $query->where('primary_tenant_id', auth()->id())
                )
                ->required(),
            Select::make('type')->options([
                NoticeType::Vacate->value => NoticeType::Vacate->getLabel(),
            ])->default(NoticeType::Vacate)->required(),
            DatePicker::make('effective_on')->required(),
            Textarea::make('body')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lease.number'),
                TextColumn::make('type')->badge(),
                TextColumn::make('effective_on')->date(),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                Action::make('cancel')
                    ->visible(fn (Notice $record) => $record->user_id === auth()->id() && $record->status === NoticeStatus::Submitted)
                    ->action(fn (Notice $record) => $record->update(['status' => NoticeStatus::Cancelled])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageNotices::route('/'),
        ];
    }
}
