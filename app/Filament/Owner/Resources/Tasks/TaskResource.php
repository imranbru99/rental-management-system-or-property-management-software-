<?php

namespace App\Filament\Owner\Resources\Tasks;

use App\Enums\TaskStatus;
use App\Filament\Owner\Resources\Tasks\Pages\ManageTasks;
use App\Models\Task;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            Textarea::make('description'),
            Select::make('property_id')->relationship('property', 'name')->searchable(),
            Select::make('assigned_to')->relationship('assignee', 'name')->searchable(),
            Select::make('status')->options(TaskStatus::class)->required(),
            DatePicker::make('due_on'),
            Select::make('created_by')->relationship('creator', 'name')->default(fn () => auth()->id())->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('assignee.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('due_on')->date(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTasks::route('/'),
        ];
    }
}
