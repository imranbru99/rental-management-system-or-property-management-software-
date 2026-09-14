<?php

namespace App\Filament\Owner\Resources\Notices;

use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Filament\Owner\Resources\Notices\Pages\ManageNotices;
use App\Models\Notice;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NoticeResource extends Resource
{
    protected static ?string $model = Notice::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|\UnitEnum|null $navigationGroup = 'Leasing';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('lease_id')->relationship('lease', 'number')->required()->searchable(),
            Select::make('user_id')->relationship('user', 'name')->required()->searchable()->default(fn () => auth()->id()),
            Select::make('type')->options(NoticeType::class)->required(),
            DatePicker::make('effective_on')->required(),
            Select::make('status')->options(NoticeStatus::class)->required(),
            Textarea::make('body')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lease.number'),
                TextColumn::make('user.name'),
                TextColumn::make('type')->badge(),
                TextColumn::make('effective_on')->date(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('type')->options(NoticeType::class),
                SelectFilter::make('status')->options(NoticeStatus::class),
            ])
            ->recordActions([
                Action::make('acknowledge')
                    ->visible(fn (Notice $record) => $record->status === NoticeStatus::Submitted)
                    ->action(fn (Notice $record) => $record->update(['status' => NoticeStatus::Acknowledged])),
                Action::make('accept')
                    ->visible(fn (Notice $record) => in_array($record->status, [NoticeStatus::Submitted, NoticeStatus::Acknowledged], true))
                    ->action(fn (Notice $record) => $record->update(['status' => NoticeStatus::Accepted])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageNotices::route('/'),
        ];
    }
}
