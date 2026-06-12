<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceConversation;
use App\Models\EcommerceConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    /**
     * Get contacts/users for chat
     */
    public function contacts(Request $request)
    {
        $conversations = $this->conversationQuery()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'contacts' => $conversations->map(fn (EcommerceConversation $conversation) => $this->contactPayload($conversation))->values(),
                'groups' => [],
                'conversations' => $conversations->mapWithKeys(fn (EcommerceConversation $conversation) => [
                    (string) $conversation->id => $this->conversationPayload($conversation),
                ]),
            ],
        ]);
    }

    /**
     * Get messages for a contact
     */
    public function messages(Request $request, $contactId)
    {
        $conversation = $this->findConversation((int) $contactId);

        $conversation->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        $conversation->load([
            'user:id,name,email',
            'messages.sender:id,name,email',
            'latestMessage.sender:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $conversation->id,
                'messages' => $conversation->messages
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn (EcommerceConversationMessage $message) => $this->messagePayload($message, $conversation)),
            ],
        ]);
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request, $contactId)
    {
        $request->validate([
            'message' => 'required|string',
            'type' => 'nullable|string|in:text,file,image,audio',
        ]);

        $conversation = $this->findConversation((int) $contactId);
        $now = Carbon::now();

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()?->id,
            'sender_type' => 'admin',
            'message' => $request->string('message')->toString(),
        ]);

        $conversation->update([
            'status' => $conversation->status === 'closed' ? 'open' : $conversation->status,
            'last_admin_message_at' => $now,
            'last_message_at' => $now,
            'closed_at' => null,
        ]);

        $message->load('sender:id,name,email');
        $conversation->load(['user:id,name,email', 'latestMessage.sender:id,name,email']);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $this->messagePayload($message, $conversation),
                'contact' => $this->contactPayload($conversation->fresh([
                    'user:id,name,email',
                    'latestMessage.sender:id,name,email',
                ])),
            ],
        ], 201);
    }

    private function conversationQuery()
    {
        return EcommerceConversation::query()
            ->with([
                'user:id,name,email',
                'messages.sender:id,name,email',
                'latestMessage.sender:id,name,email',
            ])
            ->withCount([
                'messages as unread_customer_messages_count' => fn ($messages) => $messages
                    ->where('sender_type', 'customer')
                    ->whereNull('read_at'),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at');
    }

    private function findConversation(int $conversationId): EcommerceConversation
    {
        return $this->conversationQuery()->findOrFail($conversationId);
    }

    private function contactPayload(EcommerceConversation $conversation): array
    {
        $customerName = $conversation->user?->name ?: ('Customer #' . $conversation->user_id);
        $latestMessage = $conversation->latestMessage;

        return [
            'id' => $conversation->id,
            'name' => $customerName,
            'avatarUrl' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . rawurlencode($customerName),
            'lastMessage' => $latestMessage?->message ?: $conversation->subject,
            'timestamp' => optional($conversation->last_message_at ?: $latestMessage?->created_at)->format('g:iA') ?: '',
            'unreadCount' => (int) ($conversation->unread_customer_messages_count ?? 0),
            'onlineStatus' => false,
            'email' => $conversation->user?->email,
            'status' => $conversation->status,
            'sharedFiles' => [],
            'photos' => [],
        ];
    }

    private function conversationPayload(EcommerceConversation $conversation): array
    {
        return [
            'contactId' => $conversation->id,
            'contact' => $this->contactPayload($conversation),
            'messages' => $conversation->messages
                ->sortBy('created_at')
                ->values()
                ->map(fn (EcommerceConversationMessage $message) => $this->messagePayload($message, $conversation)),
            'sharedFiles' => [],
            'photos' => [],
        ];
    }

    private function messagePayload(EcommerceConversationMessage $message, EcommerceConversation $conversation): array
    {
        $isAdminMessage = $message->sender_type === 'admin';
        $senderName = $isAdminMessage
            ? 'You'
            : ($message->sender?->name ?: ($conversation->user?->name ?: 'Customer'));

        return [
            'id' => $message->id,
            'senderId' => $isAdminMessage ? 0 : (int) ($message->sender_id ?: $conversation->user_id),
            'senderName' => $senderName,
            'senderAvatarUrl' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . rawurlencode($senderName),
            'content' => $message->message,
            'timestamp' => optional($message->created_at)->format('g:iA') ?: '',
            'type' => 'text',
            'isRead' => $isAdminMessage || $message->read_at !== null,
        ];
    }
}
