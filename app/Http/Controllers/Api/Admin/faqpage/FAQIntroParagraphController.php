<?php

namespace App\Http\Controllers\Api\Admin\faqpage;

use App\Http\Controllers\Controller;
use App\Models\FAQIntroParagraph;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FAQIntroParagraphController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get FAQ intro paragraph content.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = FAQIntroParagraph::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'faq_intro_paragraph' => null,
                    'message' => 'FAQ intro paragraph not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'faq_intro_paragraph' => [
                    'id' => $section->id,
                    'paragraph_text' => $section->paragraph_text,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update FAQ intro paragraph content.
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
                    'message' => 'Unauthorized. Only admins can manage FAQ intro paragraph content.',
                ], 403);
            }

            $validated = $request->validate([
                'paragraph_text' => ['nullable', 'string'],
            ]);

            $section = FAQIntroParagraph::updateOrCreate(
                ['id' => FAQIntroParagraph::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('faq-intro-paragraph', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAQ intro paragraph updated successfully',
                'data' => [
                    'faq_intro_paragraph' => [
                        'id' => $section->id,
                        'paragraph_text' => $section->paragraph_text,
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
                'message' => 'FAQ intro paragraph update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during FAQ intro paragraph update.',
            ], 500);
        }
    }

    /**
     * Update FAQ intro paragraph content.
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
                    'message' => 'Unauthorized. Only admins can manage FAQ intro paragraph content.',
                ], 403);
            }

            $section = FAQIntroParagraph::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ intro paragraph not found.',
                ], 404);
            }

            $dataToUpdate = [];

            if ($request->filled('paragraph_text')) {
                $dataToUpdate['paragraph_text'] = $request->input('paragraph_text');
            } elseif ($request->has('paragraph_text')) {
                $dataToUpdate['paragraph_text'] = $request->input('paragraph_text');
            }

            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['paragraph_text'])) {
                    $rules['paragraph_text'] = ['nullable', 'string'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                $this->broadcastContentUpdate('faq-intro-paragraph', 'updated', [
                    'id' => $section->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'FAQ intro paragraph updated successfully',
                'data' => [
                    'faq_intro_paragraph' => [
                        'id' => $section->id,
                        'paragraph_text' => $section->paragraph_text,
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
                'message' => 'FAQ intro paragraph update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during FAQ intro paragraph update.',
            ], 500);
        }
    }

    /**
     * Show a specific FAQ intro paragraph by ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = FAQIntroParagraph::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ intro paragraph not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'faq_intro_paragraph' => [
                    'id' => $section->id,
                    'paragraph_text' => $section->paragraph_text,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete FAQ intro paragraph content.
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
                    'message' => 'Unauthorized. Only admins can delete FAQ intro paragraph content.',
                ], 403);
            }

            $section = FAQIntroParagraph::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ intro paragraph not found.',
                ], 404);
            }

            $sectionId = $section->id;
            $section->delete();

            $this->broadcastContentUpdate('faq-intro-paragraph', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAQ intro paragraph deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ intro paragraph deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during FAQ intro paragraph deletion.',
            ], 500);
        }
    }
}

