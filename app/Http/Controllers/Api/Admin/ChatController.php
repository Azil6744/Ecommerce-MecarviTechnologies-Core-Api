<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Get contacts/users for chat
     */
    public function contacts(Request $request)
    {
        $contacts = collect([
            [
                'id' => 1,
                'name' => 'John Doe',
                'avatar' => '/avatars/john.jpg',
                'status' => 'online',
                'lastMessage' => 'Hello there!',
                'lastMessageTime' => '10:30 AM',
            ],
        ]);

        return response()->json($contacts->values()->all());
    }

    /**
     * Get messages for a contact
     */
    public function messages(Request $request, $contactId)
    {
        $messages = collect([
            [
                'id' => 1,
                'sender' => 'John Doe',
                'message' => 'Hi, how are you?',
                'timestamp' => '2026-04-09 10:30:00',
                'type' => 'text',
            ],
        ]);

        return response()->json($messages->values()->all());
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request, $contactId)
    {
        $request->validate([
            'message' => 'required|string',
            'type' => 'string|in:text,file,image',
        ]);

        // TODO: Save message to database and broadcast via WebSocket

        return response()->json(['success' => true], 201);
    }
}