<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScheduleFormSubmission;
use Illuminate\Support\Facades\Validator;

class ScheduleFormSubmissionController extends Controller
{
    /**
     * Store a newly created schedule form submission.
     * Public endpoint - no authentication required.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'website' => 'nullable|string|max:500',
            'service_needed' => 'required|string|max:255',
            'preferred_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $submission = ScheduleFormSubmission::create([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'service_needed' => $request->service_needed,
                'preferred_date' => $request->preferred_date,
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Schedule form submitted successfully',
                'data' => [
                    'schedule_form_submission' => $submission
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit schedule form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of schedule form submissions.
     * Admin only endpoint.
     */
    public function index(Request $request)
    {
        try {
            $query = ScheduleFormSubmission::orderBy('created_at', 'desc');

            // Filter by read status if provided
            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('service_needed', 'like', "%{$search}%");
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
                'message' => 'Failed to fetch schedule form submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get schedule form statistics.
     * Admin only endpoint.
     */
    public function getStats()
    {
        try {
            $total = ScheduleFormSubmission::count();
            $unread = ScheduleFormSubmission::where('is_read', false)->count();
            $read = ScheduleFormSubmission::where('is_read', true)->count();

            // Recent submissions (last 7 days)
            $recent = ScheduleFormSubmission::where('created_at', '>=', now()->subDays(7))->count();

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
     * Display the specified schedule form submission.
     * Admin only endpoint.
     */
    public function show($id)
    {
        try {
            $submission = ScheduleFormSubmission::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $submission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule form submission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mark schedule form submission as read.
     * Admin only endpoint.
     */
    public function markAsRead($id)
    {
        try {
            $submission = ScheduleFormSubmission::findOrFail($id);
            $submission->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Schedule form submission marked as read',
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
     * Mark schedule form submission as unread.
     * Admin only endpoint.
     */
    public function markAsUnread($id)
    {
        try {
            $submission = ScheduleFormSubmission::findOrFail($id);
            $submission->update(['is_read' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Schedule form submission marked as unread',
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
     * Remove the specified schedule form submission.
     * Admin only endpoint.
     */
    public function destroy($id)
    {
        try {
            $submission = ScheduleFormSubmission::findOrFail($id);
            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Schedule form submission deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule form submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
