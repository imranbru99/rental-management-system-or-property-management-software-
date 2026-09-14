<?php

namespace App\Filament\Owner\Resources\Expenses;

use App\Filament\Owner\Resources\Expenses\Pages\ManageExpenses;
use App\Models\Expense;
use App\Support\Money;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->relationship('property', 'name')->searchable(),
            Select::make('vendor_id')->relationship('vendor', 'name')->searchable(),
            TextInput::make('title')->required(),
            TextInput::make('category')->default('general'),
            TextInput::make('amount')->numeric()->required(),
            TextInput::make('currency')->default('BDT')->required(),
            DatePicker::make('spent_on')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('property.name'),
                TextColumn::make('category'),
                TextColumn::make('amount')->formatStateUsing(
                    fn ($state, Expense $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('spent_on')->date(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExpenses::route('/'),
        ];
    }
}
