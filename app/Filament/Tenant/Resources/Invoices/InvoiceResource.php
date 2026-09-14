<?php

namespace App\Filament\Tenant\Resources\Invoices;

use App\Filament\Tenant\Resources\Invoices\Pages\ManageInvoices;
use App\Models\Invoice;
use App\Services\Billing\BillingService;
use App\Services\Documents\InvoicePdfService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Billing';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('payer_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number'),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('total')->formatStateUsing(
                    fn ($state, Invoice $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('due_date')->date(),
            ])
            ->recordActions([
                Action::make('pay')
                    ->form([TextInput::make('amount')->numeric()->required()])
                    ->action(function (Invoice $record, array $data, BillingService $billing): void {
                        $billing->recordPayment($record, (int) $data['amount'], ['method' => 'card', 'gateway' => 'portal']);
                        Notification::make()->title('Payment submitted')->success()->send();
                    }),
                Action::make('pdf')
                    ->label('PDF')
                    ->action(fn (Invoice $record, InvoicePdfService $pdf) => $pdf->download($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInvoices::route('/'),
        ];
    }
}
