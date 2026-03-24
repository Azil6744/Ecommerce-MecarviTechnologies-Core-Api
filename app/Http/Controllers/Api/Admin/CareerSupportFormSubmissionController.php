<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CareerSupportFormSubmission;
use Illuminate\Support\Facades\Validator;

class CareerSupportFormSubmissionController extends Controller
{
    /**
     * Store a newly created career support form submission.
     * Public endpoint - no authentication required.
     */
    public function store(Request $request)
    {
        // Define allowed values for dropdown fields
        $allowedContactMethods = ['email', 'phone', 'sms', 'whatsapp', 'any'];
        $allowedContactTimes = ['morning', 'afternoon', 'evening', 'anytime'];

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'job_location' => 'nullable|string|max:255',
            'preferred_contact_method' => 'nullable|string|in:' . implode(',', $allowedContactMethods),
            'best_time_to_contact' => 'nullable|string|in:' . implode(',', $allowedContactTimes),
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
            $submission = CareerSupportFormSubmission::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'job_location' => $request->job_location,
                'preferred_contact_method' => $request->preferred_contact_method,
                'best_time_to_contact' => $request->best_time_to_contact,
                'message' => $request->message,
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career support form submitted successfully',
                'data' => [
                    'career_support_form_submission' => $submission
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit career support form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of career support form submissions.
     * Admin only endpoint.
     */
    public function index(Request $request)
    {
        try {
            $query = CareerSupportFormSubmission::orderBy('created_at', 'desc');

            // Filter by read status if provided
            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('job_location', 'like', "%{$search}%")
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
                'message' => 'Failed to fetch career support form submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get career support form statistics.
     * Admin only endpoint.
     */
    public function getStats()
    {
        try {
            $total = CareerSupportFormSubmission::count();
            $unread = CareerSupportFormSubmission::where('is_read', false)->count();
            $read = CareerSupportFormSubmission::where('is_read', true)->count();
            
            // Recent submissions (last 7 days)
            $recent = CareerSupportFormSubmission::where('created_at', '>=', now()->subDays(7))->count();

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
     * Display the specified career support form submission.
     * Admin only endpoint.
     */
    public function show($id)
    {
        try {
            $submission = CareerSupportFormSubmission::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $submission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Career support form submission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mark career support form submission as read.
     * Admin only endpoint.
     */
    public function markAsRead($id)
    {
        try {
            $submission = CareerSupportFormSubmission::findOrFail($id);
            $submission->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Career support form submission marked as read',
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
     * Mark career support form submission as unread.
     * Admin only endpoint.
     */
    public function markAsUnread($id)
    {
        try {
            $submission = CareerSupportFormSubmission::findOrFail($id);
            $submission->update(['is_read' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Career support form submission marked as unread',
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
     * Remove the specified career support form submission.
     * Admin only endpoint.
     */
    public function destroy($id)
    {
        try {
            $submission = CareerSupportFormSubmission::findOrFail($id);
            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Career support form submission deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete career support form submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
