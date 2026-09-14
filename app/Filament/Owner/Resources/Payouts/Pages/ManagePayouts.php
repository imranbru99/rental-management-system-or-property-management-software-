<?php

namespace App\Filament\Owner\Resources\Payouts\Pages;

use App\Filament\Owner\Resources\Payouts\PayoutResource;
use App\Services\Billing\PayoutService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManagePayouts extends ManageRecords
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('schedule')
                ->label('Schedule payout')
                ->form([
                    TextInput::make('destination')->default('bank')->required(),
                ])
                ->action(function (array $data, PayoutService $payouts): void {
                    $payouts->schedule(Filament::getTenant(), $data['destination']);
                    Notification::make()->title('Payout scheduled')->success()->send();
                }),
        ];
    }
}
