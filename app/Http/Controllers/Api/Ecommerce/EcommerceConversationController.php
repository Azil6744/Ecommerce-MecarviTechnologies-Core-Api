<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Ecommerce\Concerns\FormatsConversationPayloads;
use App\Models\EcommerceConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EcommerceConversationController extends Controller
{
    use FormatsConversationPayloads;

    public function index(Request $request)
    {
        $user = $request->user();
        $query = EcommerceConversation::query()
            ->where('user_id', $user->id)
            ->with(['user:id,name,email', 'latestMessage.sender:id,name,email'])
            ->withCount('messages');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->lower());
        }

        if ($request->filled('search')) {
            $search = '%' . trim((string) $request->query('search')) . '%';
            $query->where(function ($nested) use ($search) {
                $nested->where('subject', 'like', $search)
                    ->orWhere('linked_label', 'like', $search)
                    ->orWhereHas('messages', fn ($messages) => $messages->where('message', 'like', $search));
            });
        }

        $conversations = $query
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 20));

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'linked_type' => ['nullable', 'string', 'max:80'],
            'linked_id' => ['nullable', 'integer'],
            'linked_label' => ['nullable', 'string', 'max:255'],
        ]);

        $now = Carbon::now();
        $userId = $request->user()->id;

        // Check if user already has an existing conversation
        $conversation = EcommerceConversation::where('user_id', $userId)->first();

        if ($conversation) {
            // Update existing conversation
            $conversation->update([
                'status' => 'open',
                'subject' => $validated['subject'] ?? $conversation->subject ?? 'General Support',
                'linked_type' => $validated['linked_type'] ?? $conversation->linked_type,
                'linked_id' => $validated['linked_id'] ?? $conversation->linked_id,
                'linked_label' => $validated['linked_label'] ?? $conversation->linked_label,
                'last_customer_message_at' => $now,
                'last_message_at' => $now,
                'closed_at' => null,
            ]);
        } else {
            // Create a new one
            $conversation = EcommerceConversation::create([
                'user_id' => $userId,
                'subject' => $validated['subject'] ?? 'General Support',
                'status' => 'open',
                'linked_type' => $validated['linked_type'] ?? null,
                'linked_id' => $validated['linked_id'] ?? null,
                'linked_label' => $validated['linked_label'] ?? null,
                'last_customer_message_at' => $now,
                'last_message_at' => $now,
            ]);
        }

        $conversation->messages()->create([
            'sender_id' => $userId,
            'sender_type' => 'customer',
            'message' => $validated['message'],
        ]);

        $conversation->load($this->conversationRelations());

        return response()->json(['data' => $this->conversationPayload($conversation)], 201);
    }

    public function show(Request $request, EcommerceConversation $conversation)
    {
        $this->authorizeCustomer($request, $conversation);
        $conversation->load($this->conversationRelations());

        return response()->json(['data' => $this->conversationPayload($conversation)]);
    }

    public function addMessage(Request $request, EcommerceConversation $conversation)
    {
        $this->authorizeCustomer($request, $conversation);
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $now = Carbon::now();
        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'sender_type' => 'customer',
            'message' => $validated['message'],
        ]);

        $conversation->update([
            'status' => $conversation->status === 'closed' ? 'open' : $conversation->status,
            'last_customer_message_at' => $now,
            'last_message_at' => $now,
            'closed_at' => null,
        ]);

        $conversation->load($this->conversationRelations());

        return response()->json(['data' => $this->conversationPayload($conversation)]);
    }

    public function close(Request $request, EcommerceConversation $conversation)
    {
        $this->authorizeCustomer($request, $conversation);
        $conversation->update([
            'status' => 'closed',
            'closed_at' => Carbon::now(),
        ]);

        $conversation->load($this->conversationRelations());

        return response()->json(['data' => $this->conversationPayload($conversation)]);
    }

    private function authorizeCustomer(Request $request, EcommerceConversation $conversation): void
    {
        abort_unless((int) $conversation->user_id === (int) $request->user()->id, 403, 'You cannot access this conversation.');
    }
}
