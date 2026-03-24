<?php

namespace App\Http\Controllers\Api\Admin\ContactPage;

use App\Http\Controllers\Controller;
use App\Models\ContactPageHeroSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContactPageHeroSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get all contact page hero sections.
     * 
     * Returns all active contact page hero sections.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $heroSections = ContactPageHeroSection::active()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'hero_sections' => $heroSections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'heading' => $section->heading,
                        'subheading' => $section->subheading,
                        'description' => $section->description,
                        'is_active' => $section->is_active,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ];
                }),
            ],
        ], 200);
    }

    /**
     * Get a specific contact page hero section.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = ContactPageHeroSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Contact page hero section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hero_section' => [
                    'id' => $section->id,
                    'heading' => $section->heading,
                    'subheading' => $section->subheading,
                    'description' => $section->description,
                    'is_active' => $section->is_active,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create a new contact page hero section.
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
                    'message' => 'Unauthorized. Only admins can manage contact page hero sections.',
                ], 403);
            }

            $validated = $request->validate([
                'heading' => ['required', 'string', 'max:255'],
                'subheading' => ['nullable', 'string', 'max:500'],
                'description' => ['nullable', 'string'],
                'is_active' => ['boolean'],
            ]);

            // Set default values
            $validated['is_active'] = $validated['is_active'] ?? true;

            $heroSection = ContactPageHeroSection::create($validated);

            $this->broadcastContentUpdate('contact-page-hero-sections', 'created', [
                'id' => $heroSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact page hero section created successfully',
                'data' => [
                    'hero_section' => [
                        'id' => $heroSection->id,
                        'heading' => $heroSection->heading,
                        'subheading' => $heroSection->subheading,
                        'description' => $heroSection->description,
                        'is_active' => $heroSection->is_active,
                        'created_at' => $heroSection->created_at,
                        'updated_at' => $heroSection->updated_at,
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
                'message' => 'Failed to create contact page hero section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a contact page hero section.
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
                    'message' => 'Unauthorized. Only admins can manage contact page hero sections.',
                ], 403);
            }

            $heroSection = ContactPageHeroSection::find($id);

            if (!$heroSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact page hero section not found.',
                ], 404);
            }

            // Simple validation and update
            $validated = $request->validate([
                'heading' => ['sometimes', 'string', 'max:255'],
                'subheading' => ['nullable', 'string', 'max:500'],
                'description' => ['nullable', 'string'],
                'is_active' => ['boolean'],
            ]);

            if (empty($validated)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided for update',
                ], 400);
            }

            $heroSection->update($validated);
            
            // Refresh the model to get the updated data
            $heroSection->refresh();

            $this->broadcastContentUpdate('contact-page-hero-sections', 'updated', [
                'id' => $heroSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact page hero section updated successfully',
                'data' => [
                    'hero_section' => [
                        'id' => $heroSection->id,
                        'heading' => $heroSection->heading,
                        'subheading' => $heroSection->subheading,
                        'description' => $heroSection->description,
                        'is_active' => $heroSection->is_active,
                        'created_at' => $heroSection->created_at,
                        'updated_at' => $heroSection->updated_at,
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
                'message' => 'Failed to update contact page hero section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a contact page hero section.
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
                    'message' => 'Unauthorized. Only admins can manage contact page hero sections.',
                ], 403);
            }

            $heroSection = ContactPageHeroSection::find($id);

            if (!$heroSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact page hero section not found.',
                ], 404);
            }

            $heroSection->delete();

            $this->broadcastContentUpdate('contact-page-hero-sections', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact page hero section deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contact page hero section',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
