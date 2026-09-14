<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\Lease;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;

class MessagingService
{
    /**
     * @param  list<int>  $participantIds
     */
    public function start(array $attributes, array $participantIds, User $author, string $body): Conversation
    {
        $conversation = Conversation::query()->create($attributes);

        $ids = collect($participantIds)
            ->push($author->id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $conversation->participants()->sync($ids);

        $this->reply($conversation, $author, $body);

        return $conversation->refresh();
    }

    public function reply(Conversation $conversation, User $author, string $body): Message
    {
        $message = $conversation->messages()->create([
            'user_id' => $author->id,
            'body' => $body,
            'channel' => 'in_app',
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->participants()->syncWithoutDetaching([$author->id]);

        return $message;
    }

    public function forLease(Lease $lease, User $author, string $subject, string $body): Conversation
    {
        $existing = Conversation::query()
            ->where('lease_id', $lease->id)
            ->where('subject', $subject)
            ->first();

        if ($existing) {
            $this->reply($existing, $author, $body);

            return $existing->refresh();
        }

        $participants = Collection::make([$lease->primary_tenant_id, $author->id])
            ->merge($lease->tenants()->pluck('users.id'))
            ->unique()
            ->all();

        return $this->start([
            'organization_id' => $lease->organization_id,
            'property_id' => $lease->property_id,
            'lease_id' => $lease->id,
            'subject' => $subject,
        ], $participants, $author, $body);
    }
}
