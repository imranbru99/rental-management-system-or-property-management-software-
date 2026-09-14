<?php

namespace App\Filament\Tenant\Resources\Notices\Pages;

use App\Enums\NoticeStatus;
use App\Filament\Tenant\Resources\Notices\NoticeResource;
use App\Models\Lease;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNotices extends ManageRecords
{
    protected static string $resource = NoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Notice to vacate')
                ->mutateDataUsing(function (array $data): array {
                    $lease = Lease::query()->find($data['lease_id']);
                    $data['organization_id'] = $lease?->organization_id;
                    $data['user_id'] = auth()->id();
                    $data['status'] = NoticeStatus::Submitted;

                    return $data;
                }),
        ];
    }
}
