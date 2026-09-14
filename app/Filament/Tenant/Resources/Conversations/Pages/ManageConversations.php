<?php

namespace App\Filament\Tenant\Resources\Conversations\Pages;

use App\Filament\Tenant\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\Lease;
use App\Services\Messaging\MessagingService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageConversations extends ManageRecords
{
    protected static string $resource = ConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data, MessagingService $messages): Conversation {
                    $lease = Lease::query()->findOrFail($data['lease_id']);
                    $ownerId = $lease->property?->owner_user_id;

                    return $messages->start(
                        [
                            'organization_id' => $lease->organization_id,
                            'property_id' => $lease->property_id,
                            'lease_id' => $lease->id,
                            'subject' => $data['subject'],
                        ],
                        array_filter([$ownerId, $lease->primary_tenant_id]),
                        auth()->user(),
                        $data['first_message'],
                    );
                }),
        ];
    }
}
