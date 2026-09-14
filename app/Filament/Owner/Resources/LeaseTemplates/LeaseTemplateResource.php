<?php

namespace App\Filament\Owner\Resources\LeaseTemplates;

use App\Filament\Owner\Resources\LeaseTemplates\Pages\ManageLeaseTemplates;
use App\Models\LeaseTemplate;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeaseTemplateResource extends Resource
{
    protected static ?string $model = LeaseTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|\UnitEnum|null $navigationGroup = 'Leasing';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            Select::make('jurisdiction')->options([
                'BD' => 'Bangladesh',
                'US' => 'United States',
                'AU' => 'Australia',
                'GB' => 'United Kingdom',
                'IN' => 'India',
                'AE' => 'UAE',
            ])->required(),
            Textarea::make('body')->required()->rows(12)->columnSpanFull(),
            Toggle::make('is_default'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('jurisdiction'),
                IconColumn::make('is_default')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLeaseTemplates::route('/'),
        ];
    }
}
