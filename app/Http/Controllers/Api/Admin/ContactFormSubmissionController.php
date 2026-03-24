<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactFormSubmission;
use Illuminate\Support\Facades\Validator;

class ContactFormSubmissionController extends Controller
{
    /**
     * Store a newly created contact form submission.
     * Public endpoint - no authentication required.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $submission = ContactFormSubmission::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company' => $request->company,
                'message' => $request->message,
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact form submitted successfully',
                'data' => [
                    'contact_form_submission' => $submission
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit contact form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of contact form submissions.
     * Admin only endpoint.
     */
    public function index(Request $request)
    {
        try {
            $query = ContactFormSubmission::orderBy('created_at', 'desc');

            // Filter by read status if provided
            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");
                });
            }

            $submissions = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $submissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contact form submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get contact form statistics.
     * Admin only endpoint.
     */
    public function getStats()
    {
        try {
            $total = ContactFormSubmission::count();
            $unread = ContactFormSubmission::where('is_read', false)->count();
            $read = ContactFormSubmission::where('is_read', true)->count();
            
            // Recent submissions (last 7 days)
            $recent = ContactFormSubmission::where('created_at', '>=', now()->subDays(7))->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_submissions' => $total,
                    'unread_submissions' => $unread,
                    'read_submissions' => $read,
                    'recent_submissions' => $recent,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified contact form submission.
     * Admin only endpoint.
     */
    public function show($id)
    {
        try {
            $submission = ContactFormSubmission::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $submission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contact form submission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mark contact form submission as read.
     * Admin only endpoint.
     */
    public function markAsRead($id)
    {
        try {
            $submission = ContactFormSubmission::findOrFail($id);
            $submission->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Contact form submission marked as read',
                'data' => $submission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark contact form submission as unread.
     * Admin only endpoint.
     */
    public function markAsUnread($id)
    {
        try {
            $submission = ContactFormSubmission::findOrFail($id);
            $submission->update(['is_read' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Contact form submission marked as unread',
                'data' => $submission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as unread',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified contact form submission.
     * Admin only endpoint.
     */
    public function destroy($id)
    {
        try {
            $submission = ContactFormSubmission::findOrFail($id);
            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact form submission deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contact form submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
