<?php

namespace App\Http\Controllers\Api\Ecommerce\Concerns;

use App\Models\EcommerceConversation;
use App\Models\EcommerceConversationMessage;

trait FormatsConversationPayloads
{
    protected function conversationRelations(): array
    {
        return [
            'user:id,name,email',
            'latestMessage.sender:id,name,email',
            'messages.sender:id,name,email',
        ];
    }

    protected function conversationPayload(EcommerceConversation $conversation, bool $includeMessages = true): array
    {
        $latestMessage = $conversation->relationLoaded('latestMessage') ? $conversation->latestMessage : null;

        $payload = [
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'linked_type' => $conversation->linked_type,
            'linked_id' => $conversation->linked_id,
            'linked_label' => $conversation->linked_label,
            'last_customer_message_at' => optional($conversation->last_customer_message_at)->toISOString(),
            'last_admin_message_at' => optional($conversation->last_admin_message_at)->toISOString(),
            'last_message_at' => optional($conversation->last_message_at)->toISOString(),
            'closed_at' => optional($conversation->closed_at)->toISOString(),
            'created_at' => optional($conversation->created_at)->toISOString(),
            'updated_at' => optional($conversation->updated_at)->toISOString(),
            'customer' => $conversation->user ? [
                'id' => $conversation->user->id,
                'name' => $conversation->user->name,
                'email' => $conversation->user->email,
            ] : null,
            'latest_message' => $latestMessage ? $this->messagePayload($latestMessage) : null,
            'messages_count' => $conversation->messages_count ?? ($conversation->relationLoaded('messages') ? $conversation->messages->count() : null),
            'unread_customer_messages_count' => (int) ($conversation->unread_customer_messages_count ?? 0),
        ];

        if ($includeMessages) {
            $payload['messages'] = $conversation->relationLoaded('messages')
                ? $conversation->messages->map(fn ($message) => $this->messagePayload($message))->values()
                : [];
        }

        return $payload;
    }

    protected function messagePayload(EcommerceConversationMessage $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'sender_type' => $message->sender_type,
            'message' => $message->message,
            'read_at' => optional($message->read_at)->toISOString(),
            'created_at' => optional($message->created_at)->toISOString(),
            'updated_at' => optional($message->updated_at)->toISOString(),
            'sender' => $message->sender ? [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'email' => $message->sender->email,
            ] : null,
        ];
    }
}
