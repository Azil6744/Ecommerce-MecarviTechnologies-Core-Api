<?php

namespace App\Http\Controllers\Api\Admin\faqpage;

use App\Http\Controllers\Controller;
use App\Models\UserSubmittedQuestion;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserSubmittedQuestionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all user submitted questions.
     * 
     * Returns all user submitted questions ordered by created_at (newest first).
     * Can filter by status.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = UserSubmittedQuestion::orderBy('created_at', 'desc');
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        $questions = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user_submitted_questions' => $questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'name' => $question->name,
                        'email' => $question->email,
                        'question_message' => $question->question_message,
                        'status' => $question->status,
                        'created_at' => $question->created_at,
                        'updated_at' => $question->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific user submitted question by ID.
     * 
     * Returns a single user submitted question.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $question = UserSubmittedQuestion::find($id);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'User submitted question not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_submitted_question' => [
                    'id' => $question->id,
                    'name' => $question->name,
                    'email' => $question->email,
                    'question_message' => $question->question_message,
                    'status' => $question->status,
                    'created_at' => $question->created_at,
                    'updated_at' => $question->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new user submitted question (Public).
     * 
     * This endpoint is for users to submit questions from the website form.
     * No authentication required.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'question_message' => ['required', 'string'],
            ]);

            $question = UserSubmittedQuestion::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'question_message' => $validated['question_message'],
                'status' => 'pending',
            ]);

            $this->broadcastContentUpdate('user-submitted-question', 'created', [
                'id' => $question->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your question has been submitted successfully. We will review it and get back to you soon.',
                'data' => [
                    'user_submitted_question' => [
                        'id' => $question->id,
                        'name' => $question->name,
                        'email' => $question->email,
                        'question_message' => $question->question_message,
                        'status' => $question->status,
                        'created_at' => $question->created_at,
                    ],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Question submission failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during question submission.',
            ], 500);
        }
    }

    /**
     * Update user submitted question status (Admin Only).
     * 
     * Updates the status of a user submitted question.
     * Only super admin and editor can access this.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can update user submitted questions.',
                ], 403);
            }

            $question = UserSubmittedQuestion::find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'User submitted question not found.',
                ], 404);
            }

            $dataToUpdate = [];

            if ($request->has('status')) {
                $request->validate([
                    'status' => ['required', 'string', 'in:pending,answered,dismissed'],
                ]);
                $dataToUpdate['status'] = $request->input('status');
            }

            // Allow updating name, email, and question_message if needed
            if ($request->filled('name')) {
                $dataToUpdate['name'] = $request->input('name');
            }
            if ($request->filled('email')) {
                $dataToUpdate['email'] = $request->input('email');
            }
            if ($request->filled('question_message')) {
                $dataToUpdate['question_message'] = $request->input('question_message');
            }

            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['name'])) {
                    $rules['name'] = ['required', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['email'])) {
                    $rules['email'] = ['nullable', 'string', 'email', 'max:255'];
                }
                if (isset($dataToUpdate['question_message'])) {
                    $rules['question_message'] = ['required', 'string'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                $question->fill($dataToUpdate);
                $question->save();
                $question->refresh();

                $this->broadcastContentUpdate('user-submitted-question', 'updated', [
                    'id' => $question->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'User submitted question updated successfully',
                'data' => [
                    'user_submitted_question' => [
                        'id' => $question->id,
                        'name' => $question->name,
                        'email' => $question->email,
                        'question_message' => $question->question_message,
                        'status' => $question->status,
                        'updated_at' => $question->updated_at,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User submitted question update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during user submitted question update.',
            ], 500);
        }
    }

    /**
     * Delete user submitted question (Admin Only).
     * 
     * Deletes the user submitted question.
     * Only super admin and editor can access this.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete user submitted questions.',
                ], 403);
            }

            $question = UserSubmittedQuestion::find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'User submitted question not found.',
                ], 404);
            }

            $questionId = $question->id;
            $question->delete();

            $this->broadcastContentUpdate('user-submitted-question', 'deleted', [
                'id' => $questionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User submitted question deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User submitted question deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during user submitted question deletion.',
            ], 500);
        }
    }
}

