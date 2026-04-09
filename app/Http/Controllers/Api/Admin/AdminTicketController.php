<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceTicket;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
    /**
     * Get all support tickets
     */
    public function index(Request $request)
    {
        $query = EcommerceTicket::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->paginate($request->get('per_page', 15));

        return response()->json($tickets);
    }

    /**
     * Show ticket details
     */
    public function show(EcommerceTicket $ticket)
    {
        return response()->json($ticket->load('user', 'replies'));
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, EcommerceTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,waiting_customer,resolved,closed',
        ]);

        $ticket->update(['status' => $request->status]);

        return response()->json($ticket);
    }

    /**
     * Add reply to ticket
     */
    public function addReply(Request $request, EcommerceTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'admin_reply' => true,
            'message' => $request->message,
            'attachments' => $request->attachments,
        ]);

        return response()->json($ticket->load('replies'));
    }

    /**
     * Close ticket
     */
    public function close(EcommerceTicket $ticket)
    {
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        return response()->json($ticket);
    }
}