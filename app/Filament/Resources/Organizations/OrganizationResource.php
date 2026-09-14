<?php

namespace App\Filament\Resources\Organizations;

use App\Enums\OrganizationStatus;
use App\Enums\OrganizationType;
use App\Filament\Resources\Organizations\Pages\ManageOrganizations;
use App\Models\Organization;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Organization')->schema([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('owner_user_id')->relationship('owner', 'name')->searchable()->required(),
                Select::make('plan_id')->relationship('plan', 'name'),
                Select::make('type')->options(OrganizationType::class)->required(),
                Select::make('status')->options(OrganizationStatus::class)->required(),
                TextInput::make('email')->email(),
                TextInput::make('phone'),
                TextInput::make('country')->maxLength(8)->default('BD'),
                TextInput::make('currency')->maxLength(8)->default('BDT'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('owner.name')->label('Owner'),
                TextColumn::make('plan.name'),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('currency'),
                TextColumn::make('created_at')->dateTime()->since(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrganizationStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrganizations::route('/'),
        ];
    }
}
