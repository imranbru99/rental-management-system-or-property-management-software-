<?php

namespace App\Filament\Owner\Resources\MaintenanceRequests;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Filament\Owner\Resources\MaintenanceRequests\Pages\ManageMaintenanceRequests;
use App\Models\MaintenanceRequest;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->relationship('property', 'name')->required()->searchable(),
            Select::make('tenant_id')->relationship('tenant', 'name')->searchable(),
            Select::make('assigned_to')->relationship('assignee', 'name')->searchable(),
            Select::make('vendor_id')->relationship('vendor', 'name')->searchable(),
            TextInput::make('title')->required(),
            Textarea::make('description')->columnSpanFull(),
            Select::make('priority')->options(MaintenancePriority::class)->required(),
            Select::make('status')->options(MaintenanceStatus::class)->required(),
            TextInput::make('category')->default('general'),
            TextInput::make('estimated_cost')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable(),
                TextColumn::make('title')->limit(40),
                TextColumn::make('property.name'),
                TextColumn::make('priority')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('sla_due_at')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->options(MaintenanceStatus::class),
                SelectFilter::make('priority')->options(MaintenancePriority::class),
            ])
            ->recordActions([
                Action::make('acknowledge')
                    ->visible(fn (MaintenanceRequest $record) => $record->status === MaintenanceStatus::Submitted)
                    ->action(fn (MaintenanceRequest $record) => $record->update(['status' => MaintenanceStatus::Acknowledged])),
                Action::make('schedule')
                    ->visible(fn (MaintenanceRequest $record) => in_array($record->status, [MaintenanceStatus::Submitted, MaintenanceStatus::Acknowledged], true))
                    ->form([DateTimePicker::make('scheduled_at')->required()])
                    ->action(fn (MaintenanceRequest $record, array $data) => $record->update([
                        'status' => MaintenanceStatus::Scheduled,
                        'scheduled_at' => $data['scheduled_at'],
                    ])),
                Action::make('start')
                    ->visible(fn (MaintenanceRequest $record) => in_array($record->status, [MaintenanceStatus::Acknowledged, MaintenanceStatus::Scheduled], true))
                    ->action(fn (MaintenanceRequest $record) => $record->update(['status' => MaintenanceStatus::InProgress])),
                Action::make('resolve')
                    ->visible(fn (MaintenanceRequest $record) => ! in_array($record->status, [MaintenanceStatus::Resolved, MaintenanceStatus::Rated, MaintenanceStatus::Cancelled], true))
                    ->form([TextInput::make('actual_cost')->numeric()])
                    ->action(fn (MaintenanceRequest $record, array $data) => $record->update([
                        'status' => MaintenanceStatus::Resolved,
                        'actual_cost' => $data['actual_cost'] ?? $record->actual_cost,
                        'resolved_at' => now(),
                    ])),
                Action::make('comment')
                    ->form([Textarea::make('body')->required()])
                    ->action(function (MaintenanceRequest $record, array $data): void {
                        $record->comments()->create([
                            'user_id' => auth()->id(),
                            'body' => $data['body'],
                        ]);
                        Notification::make()->title('Comment added')->success()->send();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMaintenanceRequests::route('/'),
        ];
    }
}
