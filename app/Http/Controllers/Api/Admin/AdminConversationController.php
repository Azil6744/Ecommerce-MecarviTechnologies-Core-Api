<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Ecommerce\Concerns\FormatsConversationPayloads;
use App\Models\EcommerceConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminConversationController extends Controller
{
    use FormatsConversationPayloads;

    public function index(Request $request)
    {
        $query = EcommerceConversation::query()
            ->with(['user:id,name,email', 'latestMessage.sender:id,name,email'])
            ->withCount([
                'messages',
                'messages as unread_customer_messages_count' => fn ($messages) => $messages
                    ->where('sender_type', 'customer')
                    ->whereNull('read_at'),
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->lower());
        }

        if ($request->filled('search')) {
            $search = '%' . trim((string) $request->query('search')) . '%';
            $query->where(function ($nested) use ($search) {
                $nested->where('subject', 'like', $search)
                    ->orWhere('linked_label', 'like', $search)
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $search)->orWhere('email', 'like', $search))
                    ->orWhereHas('messages', fn ($messages) => $messages->where('message', 'like', $search));
            });
        }

        $conversations = $query
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 30));

        return response()->json([
            'data' => $conversations->getCollection()->map(fn ($conversation) => $this->conversationPayload($conversation, false))->values(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    public function show(EcommerceConversation $conversation)
    {
        $conversation->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        $conversation->load($this->conversationRelations());
        $conversation->loadCount([
            'messages',
            'messages as unread_customer_messages_count' => fn ($messages) => $messages
                ->where('sender_type', 'customer')
                ->whereNull('read_at'),
        ]);

        return response()->json(['data' => $this->conversationPayload($conversation)]);
    }

    public function addMessage(Request $request, EcommerceConversation $conversation)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $now = Carbon::now();
        $conversation->messages()->create([
            'sender_id' => $request->user()?->id,
            'sender_type' => 'admin',
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'status' => $conversation->status === 'closed' ? 'open' : $conversation->status,
            'last_admin_message_at' => $now,
            'last_message_at' => $now,
            'closed_at' => null,
        ]);

        $conversation->load($this->conversationRelations());
        $conversation->loadCount([
            'messages',
            'messages as unread_customer_messages_count' => fn ($messages) => $messages
                ->where('sender_type', 'customer')
                ->whereNull('read_at'),
        ]);

        return response()->json(['data' => $this->conversationPayload($conversation)]);
    }

    public function updateStatus(Request $request, EcommerceConversation $conversation)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,pending,closed'],
        ]);

        $conversation->update([
            'status' => $validated['status'],
            'closed_at' => $validated['status'] === 'closed' ? Carbon::now() : null,
        ]);

        $conversation->load($this->conversationRelations());
        $conversation->loadCount([
            'messages',
            'messages as unread_customer_messages_count' => fn ($messages) => $messages
                ->where('sender_type', 'customer')
                ->whereNull('read_at'),
        ]);

        return response()->json(['data' => $this->conversationPayload($conversation)]);
    }
}
