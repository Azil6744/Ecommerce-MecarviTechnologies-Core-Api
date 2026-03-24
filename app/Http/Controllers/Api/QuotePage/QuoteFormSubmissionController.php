<?php

namespace App\Http\Controllers\Api\QuotePage;

use App\Http\Controllers\Controller;
use App\Models\QuoteFormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class QuoteFormSubmissionController extends Controller
{
    /**
     * Store a newly created quote form submission.
     * Public endpoint - no authentication required.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'project_type' => 'nullable|string|max:255',
            'estimate_budget' => 'nullable|string|max:255',
            'maximum_time_for_project' => 'nullable|string|max:255',
            'required_skills' => 'nullable|string|max:5000',
            'page_slug' => 'nullable|string|max:255',
            'corporate_intake_payload' => 'nullable|string|max:65535',
            'uploaded_files' => 'nullable|array',
            'uploaded_files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,jpeg,png,gif,zip,rar|max:51200', // 10MB max per file
            'message' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            if (empty($data['page_slug'])) {
                $data['page_slug'] = 'quote';
            }
            
            // Handle file uploads
            $uploadedFilePaths = [];
            if ($request->hasFile('uploaded_files')) {
                foreach ($request->file('uploaded_files') as $file) {
                    $path = $file->store('quote-form-files', 'public');
                    $uploadedFilePaths[] = $path;
                }
                $data['uploaded_files'] = json_encode($uploadedFilePaths);
            } else {
                $data['uploaded_files'] = null;
            }
            
            $data['is_read'] = false;
            
            $submission = QuoteFormSubmission::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Quote form submitted successfully',
                'data' => [
                    'quote_form_submission' => $submission
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quote form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of quote form submissions.
     * Admin only endpoint.
     */
    public function index(Request $request)
    {
        try {
            $query = QuoteFormSubmission::orderBy('created_at', 'desc');

            if ($request->has('page_slug')) {
                $query->where('page_slug', $request->page_slug);
            }

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
                      ->orWhere('company_name', 'like', "%{$search}%")
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
                'message' => 'Failed to fetch quote form submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quote form statistics.
     * Admin only endpoint.
     */
    public function getStats()
    {
        try {
            $query = QuoteFormSubmission::query();
            if (request()->has('page_slug')) {
                $query->where('page_slug', request()->page_slug);
            }

            $total = (clone $query)->count();
            $unread = (clone $query)->where('is_read', false)->count();
            $read = (clone $query)->where('is_read', true)->count();
            
            // Recent submissions (last 7 days)
            $recent = (clone $query)->where('created_at', '>=', now()->subDays(7))->count();

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
     * Display the specified quote form submission.
     * Admin only endpoint.
     */
    public function show($id)
    {
        try {
            $submission = QuoteFormSubmission::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $submission
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quote form submission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mark quote form submission as read.
     * Admin only endpoint.
     */
    public function markAsRead($id)
    {
        try {
            $submission = QuoteFormSubmission::findOrFail($id);
            $submission->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Quote form submission marked as read',
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
     * Mark quote form submission as unread.
     * Admin only endpoint.
     */
    public function markAsUnread($id)
    {
        try {
            $submission = QuoteFormSubmission::findOrFail($id);
            $submission->update(['is_read' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Quote form submission marked as unread',
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
     * Remove the specified quote form submission.
     * Admin only endpoint.
     */
    public function destroy($id)
    {
        try {
            $submission = QuoteFormSubmission::findOrFail($id);
            
            // Delete uploaded files if they exist
            if ($submission->uploaded_files) {
                $files = json_decode($submission->uploaded_files, true);
                if (is_array($files)) {
                    foreach ($files as $file) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
            
            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quote form submission deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quote form submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
