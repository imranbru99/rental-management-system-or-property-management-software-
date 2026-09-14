<?php

namespace App\Services\Ai;

use ImranDevBd\AiHub\Facades\AIHub;
use Throwable;

class SupportChatService
{
    public function answer(string $question, string $audience = 'tenant'): string
    {
        $system = $audience === 'owner'
            ? 'You are RentOS assistant for property owners. Be concise. Explain leasing, rent collection, maintenance assignment, and reports.'
            : 'You are RentOS assistant for renters. Help with finding homes, paying rent, maintenance requests, and lease questions. Do not invent legal advice.';

        try {
            $request = AIHub::prompt($question)->forJob('support-chat');

            if (method_exists($request, 'system')) {
                $request->system($system);
            }

            return (string) $request->send()->content;
        } catch (Throwable) {
            return 'I could not reach the AI assistant right now. Check rent due dates in Billing, submit repairs under Maintenance, or email your property manager.';
        }
    }
}
