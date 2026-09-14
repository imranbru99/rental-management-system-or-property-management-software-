<?php

namespace App\Filament\Owner\Resources\Conversations\Pages;

use App\Filament\Owner\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
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
                    return $messages->start(
                        [
                            'property_id' => $data['property_id'] ?? null,
                            'lease_id' => $data['lease_id'] ?? null,
                            'subject' => $data['subject'],
                        ],
                        $data['participants'] ?? [],
                        auth()->user(),
                        $data['first_message'],
                    );
                }),
        ];
    }
}
