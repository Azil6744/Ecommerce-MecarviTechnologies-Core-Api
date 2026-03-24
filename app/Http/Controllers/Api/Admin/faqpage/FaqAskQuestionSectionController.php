<?php

namespace App\Http\Controllers\Api\Admin\faqpage;

use App\Http\Controllers\Controller;
use App\Models\FaqAskQuestionSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FaqAskQuestionSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get FAQ ask question form section content (heading and description).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = FaqAskQuestionSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'faq_ask_question_section' => null,
                    'message' => 'FAQ ask question section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'faq_ask_question_section' => [
                    'id' => $section->id,
                    'heading' => $section->heading,
                    'description' => $section->description,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Get FAQ ask question section by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = FaqAskQuestionSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ ask question section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'faq_ask_question_section' => [
                    'id' => $section->id,
                    'heading' => $section->heading,
                    'description' => $section->description,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update FAQ ask question section content.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage FAQ ask question section content.',
                ], 403);
            }

            $validated = $request->validate([
                'heading' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            $section = FaqAskQuestionSection::updateOrCreate(
                ['id' => FaqAskQuestionSection::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('faq-ask-question-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAQ ask question section updated successfully',
                'data' => [
                    'faq_ask_question_section' => [
                        'id' => $section->id,
                        'heading' => $section->heading,
                        'description' => $section->description,
                        'updated_at' => $section->updated_at,
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
                'message' => 'FAQ ask question section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during FAQ ask question section update.',
            ], 500);
        }
    }

    /**
     * Update FAQ ask question section content.
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
                    'message' => 'Unauthorized. Only admins can manage FAQ ask question section content.',
                ], 403);
            }

            $section = FaqAskQuestionSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ ask question section not found.',
                ], 404);
            }

            $dataToUpdate = [];

            if ($request->has('heading')) {
                $dataToUpdate['heading'] = $request->input('heading');
            }
            if ($request->has('description')) {
                $dataToUpdate['description'] = $request->input('description');
            }

            if (!empty($dataToUpdate)) {
                $request->validate([
                    'heading' => ['nullable', 'string', 'max:255'],
                    'description' => ['nullable', 'string'],
                ]);

                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                $this->broadcastContentUpdate('faq-ask-question-section', 'updated', [
                    'id' => $section->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'FAQ ask question section updated successfully',
                'data' => [
                    'faq_ask_question_section' => [
                        'id' => $section->id,
                        'heading' => $section->heading,
                        'description' => $section->description,
                        'updated_at' => $section->updated_at,
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
                'message' => 'FAQ ask question section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during FAQ ask question section update.',
            ], 500);
        }
    }
}
