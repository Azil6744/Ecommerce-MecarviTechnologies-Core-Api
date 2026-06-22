<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceProductQuestion;
use App\Models\EcommerceProductQuestionReply;

class EcommerceProductQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EcommerceProductQuestion::with([
            'replies' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'product.previewAssets' => function ($q) {
                $q->where('is_active', true);
            }
        ]);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Return all questions
        $questions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $questions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'question' => ['required', 'string'],
        ]);

        $user = $request->user();

        $question = EcommerceProductQuestion::create([
            'product_id' => $validated['product_id'],
            'user_id' => $user ? $user->id : null,
            'customer_name' => $user ? $user->name : 'Guest Customer',
            'customer_email' => $user ? $user->email : null,
            'question' => $validated['question'],
            'status' => 'unanswered',
        ]);

        // Load relations for response
        $question->load([
            'product.previewAssets' => function ($q) {
                $q->where('is_active', true);
            },
            'replies'
        ]);

        return response()->json([
            'success' => true,
            'data' => $question
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $question = EcommerceProductQuestion::with([
            'replies' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'product.previewAssets' => function ($q) {
                $q->where('is_active', true);
            }
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $question
        ]);
    }

    /**
     * Add a reply to the question.
     */
    public function addReply(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $question = EcommerceProductQuestion::findOrFail($id);
        $user = $request->user();

        // Determine role based on user admin privileges
        $role = 'customer';
        if ($user && ($user->isSuperAdmin() || $user->isEditor() || $user->hasAdminAccess())) {
            $role = 'admin';
        }

        $reply = EcommerceProductQuestionReply::create([
            'product_question_id' => $question->id,
            'user_id' => $user ? $user->id : null,
            'name' => $user ? $user->name : 'Guest Customer',
            'role' => $role,
            'content' => $validated['content'],
            'helpful_count' => 0,
        ]);

        // Update question status to answered
        $question->update(['status' => 'answered']);

        return response()->json([
            'success' => true,
            'data' => $reply
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $question = EcommerceProductQuestion::findOrFail($id);
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully.'
        ]);
    }
}
