<?php

namespace App\Filament\Owner\Resources\Leases;

use App\Enums\LeaseStatus;
use App\Filament\Owner\Resources\Leases\Pages\ManageLeases;
use App\Models\Lease;
use App\Services\Documents\LeasePdfService;
use App\Services\Leasing\LeaseService;
use App\Services\Leasing\LeaseSigningService;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeaseResource extends Resource
{
    protected static ?string $model = Lease::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Leasing';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->relationship('property', 'name')->required()->searchable(),
            Select::make('unit_id')->relationship('unit', 'code')->searchable(),
            Select::make('primary_tenant_id')->relationship('primaryTenant', 'name')->required()->searchable(),
            Select::make('template_id')->relationship('template', 'name')->searchable(),
            Select::make('status')->options(LeaseStatus::class)->required(),
            DatePicker::make('starts_on')->required(),
            DatePicker::make('ends_on')->required(),
            TextInput::make('rent')->numeric()->required(),
            TextInput::make('security_deposit')->numeric()->required(),
            TextInput::make('currency')->default('BDT')->required(),
            TextInput::make('due_day')->numeric()->default(1),
            TextInput::make('late_fee_grace_days')->numeric()->default(5),
            TextInput::make('late_fee_percent')->numeric()->default(5),
            TextInput::make('notice_days')->numeric()->default(30),
            TextInput::make('renewal_offer_days')->numeric()->default(60),
            Toggle::make('auto_renew'),
            TextInput::make('escalation_percent')->numeric()->default(0),
            Textarea::make('terms')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable(),
                TextColumn::make('property.name'),
                TextColumn::make('primaryTenant.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('rent')->formatStateUsing(
                    fn ($state, Lease $record) => Money::format((int) $state, $record->currency)
                ),
                TextColumn::make('ends_on')->date(),
            ])
            ->recordActions([
                Action::make('send')
                    ->visible(fn (Lease $record) => $record->status === LeaseStatus::Draft)
                    ->action(function (Lease $record, LeaseSigningService $signing): void {
                        $signing->send($record);
                        Notification::make()->title('Lease sent for signature')->success()->send();
                    }),
                Action::make('sign')
                    ->visible(fn (Lease $record) => in_array($record->status, [LeaseStatus::Draft, LeaseStatus::Sent, LeaseStatus::PartiallySigned], true))
                    ->action(function (Lease $record, LeaseSigningService $signing): void {
                        $signing->sign($record, auth()->user(), 'owner');
                        Notification::make()->title('Owner signature recorded')->success()->send();
                    }),
                Action::make('activate')
                    ->visible(fn (Lease $record) => $record->status !== LeaseStatus::Active)
                    ->action(function (Lease $record, LeaseService $leases): void {
                        $leases->activate($record);
                        Notification::make()->title('Lease activated and first invoices created')->success()->send();
                    }),
                Action::make('pdf')
                    ->label('PDF')
                    ->action(fn (Lease $record, LeasePdfService $pdf) => $pdf->download($record)),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLeases::route('/'),
        ];
    }
}
