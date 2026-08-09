<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceProductQuestion;
use App\Models\EcommerceProductQuestionReply;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

        $user = $request->user();
        if ($request->boolean('my_questions') || $request->has('user_id')) {
            $userId = $request->input('user_id') ?? ($user ? $user->id : null);
            if ($userId) {
                $query->where('user_id', $userId);
            } elseif ($user && $user->email) {
                $query->where('customer_email', $user->email);
            }
        } elseif ($request->has('customer_email')) {
            $query->where('customer_email', $request->input('customer_email'));
        }

        // Return questions
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
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $user = $request->user();

        $customerName = !empty($validated['customer_name'])
            ? $validated['customer_name']
            : ($user ? $user->name : 'Guest Customer');

        $customerEmail = !empty($validated['customer_email'])
            ? $validated['customer_email']
            : ($user ? $user->email : null);

        $question = EcommerceProductQuestion::create([
            'product_id' => $validated['product_id'],
            'user_id' => $user ? $user->id : null,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
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

        // Send email notification using EmailNotificationService
        try {
            $emailService = app(\App\Services\EmailNotificationService::class);
            $productName = $question->product ? $question->product->name : 'Product #' . $question->product_id;

            $emailService->sendEvent('message_from_customer', [
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'message_preview' => "Question on {$productName}: {$question->question}",
                'site_name' => config('app.name', 'Mecarvi Embroidery'),
            ], $customerEmail);
        } catch (\Throwable $e) {
            Log::warning('Failed to send product question admin notification email: ' . $e->getMessage());
        }

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
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $question = EcommerceProductQuestion::findOrFail($id);
        $user = $request->user();

        // Determine role based on user admin privileges
        $role = 'customer';
        if ($user && (
            (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
            (method_exists($user, 'isEditor') && $user->isEditor()) ||
            (method_exists($user, 'hasAdminAccess') && $user->hasAdminAccess())
        )) {
            $role = 'admin';
        }

        $replyName = !empty($validated['name'])
            ? $validated['name']
            : ($user ? $user->name : ($role === 'admin' ? 'Admin Support' : 'Guest Customer'));

        $reply = EcommerceProductQuestionReply::create([
            'product_question_id' => $question->id,
            'user_id' => $user ? $user->id : null,
            'name' => $replyName,
            'role' => $role,
            'content' => $validated['content'],
            'helpful_count' => 0,
        ]);

        // Update question status to answered if reply is by admin
        if ($role === 'admin') {
            $question->update(['status' => 'answered']);

            // Send notification email to customer if customer_email is present
            if (!empty($question->customer_email)) {
                try {
                    $emailService = app(\App\Services\EmailNotificationService::class);
                    $productName = $question->product ? $question->product->name : 'your product inquiry';
                    $payload = [
                        'customer_name' => $question->customer_name ?: 'Customer',
                        'customer_email' => $question->customer_email,
                        'product_name' => $productName,
                        'question' => $question->question,
                        'answer' => $reply->content,
                        'reply' => $reply->content,
                        'site_name' => config('app.name', 'Mecarvi Embroidery'),
                    ];

                    $emailService->sendEvent('customer_product_question', $payload, $question->customer_email);
                    $emailService->sendEvent('customer_product_question_reply', $payload, $question->customer_email);
                } catch (\Throwable $e) {
                    Log::warning('Failed to send product question reply email to customer: ' . $e->getMessage());
                }
            }
        }

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
