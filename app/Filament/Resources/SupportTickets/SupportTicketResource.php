<?php

namespace App\Filament\Resources\SupportTickets;

use App\Enums\TicketStatus;
use App\Filament\Resources\SupportTickets\Pages\ManageSupportTickets;
use App\Models\SupportTicket;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static string|\UnitEnum|null $navigationGroup = 'Support';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('subject')->required(),
            Textarea::make('body')->required()->columnSpanFull(),
            Select::make('user_id')->relationship('user', 'name')->searchable()->required(),
            Select::make('organization_id')->relationship('organization', 'name')->searchable(),
            Select::make('assignee_id')->relationship('assignee', 'name')->searchable(),
            Select::make('status')->options(TicketStatus::class)->required(),
            Select::make('priority')->options([
                'low' => 'Low',
                'normal' => 'Normal',
                'high' => 'High',
            ])->default('normal'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->searchable(),
                TextColumn::make('user.name'),
                TextColumn::make('organization.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('priority'),
                TextColumn::make('created_at')->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSupportTickets::route('/'),
        ];
    }
}
