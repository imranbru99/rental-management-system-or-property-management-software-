<?php

namespace App\Filament\Tenant\Resources\Referrals\Pages;

use App\Enums\ReferralStatus;
use App\Filament\Tenant\Resources\Referrals\ReferralResource;
use App\Models\Referral;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;

class ManageReferrals extends ManageRecords
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('New referral code')
                ->action(function (): void {
                    Referral::query()->create([
                        'referrer_id' => auth()->id(),
                        'code' => strtoupper(Str::random(8)),
                        'credit_amount' => 50000,
                        'status' => ReferralStatus::Pending,
                    ]);
                    Notification::make()->title('Referral code created')->success()->send();
                }),
        ];
    }
}
