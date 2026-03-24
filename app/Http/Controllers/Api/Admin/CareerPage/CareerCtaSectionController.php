<?php

namespace App\Http\Controllers\Api\Admin\CareerPage;

use App\Http\Controllers\Controller;
use App\Models\CareerCtaSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CareerCtaSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all career CTA sections.
     * 
     * Returns all active CTA sections ordered by sort order.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $ctaSections = CareerCtaSection::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'career_cta_sections' => $ctaSections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'title' => $section->title,
                        'description' => $section->description,
                        'button_text' => $section->button_text,
                        'button_link' => $section->button_link,
                        'is_active' => $section->is_active,
                        'sort_order' => $section->sort_order,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific career CTA section.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $ctaSection = CareerCtaSection::find($id);

        if (!$ctaSection) {
            return response()->json([
                'success' => false,
                'message' => 'Career CTA section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'career_cta_section' => [
                    'id' => $ctaSection->id,
                    'title' => $ctaSection->title,
                    'description' => $ctaSection->description,
                    'button_text' => $ctaSection->button_text,
                    'button_link' => $ctaSection->button_link,
                    'is_active' => $ctaSection->is_active,
                    'sort_order' => $ctaSection->sort_order,
                    'created_at' => $ctaSection->created_at,
                    'updated_at' => $ctaSection->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new career CTA section.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage career CTA sections.',
                ], 403);
            }

            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'button_link' => ['nullable', 'string', 'max:255'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            // Set default values
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['sort_order'] = $validated['sort_order'] ?? 0;

            $ctaSection = CareerCtaSection::create($validated);

            $this->broadcastContentUpdate('career-cta-sections', 'created', [
                'id' => $ctaSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career CTA section created successfully',
                'data' => [
                    'career_cta_section' => [
                        'id' => $ctaSection->id,
                        'title' => $ctaSection->title,
                        'description' => $ctaSection->description,
                        'button_text' => $ctaSection->button_text,
                        'button_link' => $ctaSection->button_link,
                        'is_active' => $ctaSection->is_active,
                        'sort_order' => $ctaSection->sort_order,
                        'created_at' => $ctaSection->created_at,
                        'updated_at' => $ctaSection->updated_at,
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
                'message' => 'Failed to create career CTA section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a career CTA section.
     * 
     * @param \Illuminate\Http\Request $request
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
                    'message' => 'Unauthorized. Only admins can manage career CTA sections.',
                ], 403);
            }

            $ctaSection = CareerCtaSection::find($id);

            if (!$ctaSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career CTA section not found.',
                ], 404);
            }

            $validated = $request->validate([
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'button_link' => ['nullable', 'string', 'max:255'],
                'is_active' => ['boolean'],
                'sort_order' => ['integer', 'min:0'],
            ]);

            $ctaSection->update($validated);

            $this->broadcastContentUpdate('career-cta-sections', 'updated', [
                'id' => $ctaSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career CTA section updated successfully',
                'data' => [
                    'career_cta_section' => [
                        'id' => $ctaSection->id,
                        'title' => $ctaSection->title,
                        'description' => $ctaSection->description,
                        'button_text' => $ctaSection->button_text,
                        'button_link' => $ctaSection->button_link,
                        'is_active' => $ctaSection->is_active,
                        'sort_order' => $ctaSection->sort_order,
                        'created_at' => $ctaSection->created_at,
                        'updated_at' => $ctaSection->updated_at,
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
                'message' => 'Failed to update career CTA section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a career CTA section.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $currentUser = request()->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage career CTA sections.',
                ], 403);
            }

            $ctaSection = CareerCtaSection::find($id);

            if (!$ctaSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Career CTA section not found.',
                ], 404);
            }

            $ctaSection->delete();

            $this->broadcastContentUpdate('career-cta-sections', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Career CTA section deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete career CTA section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
