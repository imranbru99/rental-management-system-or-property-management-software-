<?php

namespace App\Filament\Owner\Resources\Invoices;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Filament\Owner\Resources\Invoices\Pages\ManageInvoices;
use App\Models\Invoice;
use App\Services\Billing\BillingService;
use App\Services\Documents\InvoicePdfService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('lease_id')->relationship('lease', 'number')->searchable(),
            Select::make('property_id')->relationship('property', 'name')->searchable(),
            Select::make('payer_id')->relationship('payer', 'name')->searchable(),
            Select::make('type')->options(InvoiceType::class)->required(),
            Select::make('status')->options(InvoiceStatus::class)->required(),
            TextInput::make('total')->numeric()->required(),
            TextInput::make('amount_paid')->numeric()->default(0),
            TextInput::make('currency')->default('BDT')->required(),
            DatePicker::make('issue_date')->required(),
            DatePicker::make('due_date')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable(),
                TextColumn::make('payer.name'),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('total')->formatStateUsing(
                    fn ($state, Invoice $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('due_date')->date(),
            ])
            ->filters([
                SelectFilter::make('status')->options(InvoiceStatus::class),
            ])
            ->recordActions([
                Action::make('recordPayment')
                    ->form([
                        TextInput::make('amount')->numeric()->required(),
                    ])
                    ->action(function (Invoice $record, array $data, BillingService $billing): void {
                        $billing->recordPayment($record, (int) $data['amount'], ['method' => 'cash']);
                        Notification::make()->title('Payment recorded')->success()->send();
                    }),
                Action::make('pdf')
                    ->label('PDF')
                    ->action(fn (Invoice $record, InvoicePdfService $pdf) => $pdf->download($record)),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInvoices::route('/'),
        ];
    }
}
