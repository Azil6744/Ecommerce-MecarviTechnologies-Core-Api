<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceTicket;
use Illuminate\Support\Facades\Schema;

class EcommerceTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        // Check if admin to return all, or just user
        if ($user && $user->isSuperAdmin()) {
            return response()->json(['success' => true, 'data' => EcommerceTicket::all()]);
        }
        
        // Get by user_id if column exists, otherwise all
        if(Schema::hasColumn((new EcommerceTicket)->getTable(), 'user_id')) {
            $query = EcommerceTicket::where('user_id', $user->id);
            return response()->json(['success' => true, 'data' => $query->get()]);
        }

        return response()->json(['success' => true, 'data' => EcommerceTicket::all()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'nullable|string|in:Low,Normal,High',
        ]);

        $data = $request->only(['subject', 'message', 'priority', 'customer_name']);
        $data['user_id'] = $request->user()->id;
        $data['customer_name'] = $data['customer_name'] ?? $request->user()->name;
        $data['ticket_number'] = 'TKT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $data['status'] = 'Open';
        $data['priority'] = $data['priority'] ?? 'Normal';

        $item = EcommerceTicket::create($data);
        return response()->json(['success' => true, 'data' => $item->load('replies')], 201);
    }

    public function publicStore(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'source_page' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $ticket = EcommerceTicket::create([
            'ticket_number' => 'TKT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)),
            'user_id' => optional($request->user())->id,
            'product_id' => $validated['product_id'] ?? null,
            'customer_name' => $validated['customer_name'] ?? 'Product detail visitor',
            'subject' => $validated['subject'] ?? 'Product support request',
            'message' => $validated['message'] ?? 'Customer requested help from the product detail page.',
            'source_page' => $validated['source_page'] ?? 'product_detail',
            'status' => 'Open',
            'priority' => 'Normal',
            'metadata' => array_merge($validated['metadata'] ?? [], [
                'customer_email' => $validated['customer_email'] ?? null,
            ]),
        ]);

        return response()->json(['success' => true, 'data' => $ticket], 201);
    }

    public function show(Request $request, $id)
    {
        $item = EcommerceTicket::with('replies.user')->findOrFail($id);
        
        // Ensure user can only see their own ticket unless super admin
        $user = $request->user();
        if ($item->user_id !== $user->id && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = EcommerceTicket::findOrFail($id);
        $item->update($request->all());
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $item = EcommerceTicket::findOrFail($id);
        
        $user = $request->user();
        if ($item->user_id !== $user->id && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    public function addReply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = EcommerceTicket::findOrFail($id);
        
        $user = $request->user();
        if ($ticket->user_id !== $user->id && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'admin_reply' => $user->isSuperAdmin(), // If user is admin, set to true
            'message' => $request->message,
            'attachments' => $request->attachments,
        ]);

        // Optionally update ticket status to "open" or "customer_replied"
        if (!$user->isSuperAdmin()) {
            $ticket->update(['status' => 'Open']);
        }

        return response()->json(['success' => true, 'data' => $ticket->load('replies.user')]);
    }
}
