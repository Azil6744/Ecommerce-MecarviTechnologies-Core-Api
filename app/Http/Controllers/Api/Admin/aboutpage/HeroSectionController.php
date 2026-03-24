<?php

namespace App\Http\Controllers\Api\Admin\aboutpage;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HeroSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get hero section content.
     * 
     * Returns the current hero section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = HeroSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'hero_section' => null,
                    'message' => 'Hero section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hero_section' => [
                    'id' => $section->id,
                    'hero_background_image' => $section->hero_background_image_url,
                    'title_part_1' => $section->title_part_1,
                    'title_part_2' => $section->title_part_2,
                    'description_1' => $section->description_1,
                    'description_2' => $section->description_2,
                    'hero_image' => $section->hero_image_url,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update hero section content.
     * 
     * Creates a new hero section if none exists, or updates the existing one.
     * Only super admin and editor can access this.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Check if user has admin access (super_admin or editor)
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage hero section content.',
                ], 403);
            }

            // Validate the incoming request data
            \Log::info('HeroSection validation - Request data: ' . json_encode($request->all()));
            \Log::info('HeroSection validation - Request files: ' . json_encode($request->allFiles()));
            \Log::info('HeroSection validation - Request headers: ' . json_encode($request->header()));
            
            try {
                $validated = $request->validate([
                    'hero_background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                    'title_part_1' => ['nullable', 'string', 'max:255'],
                    'title_part_2' => ['nullable', 'string', 'max:255'],
                    'description_1' => ['nullable', 'string'],
                    'description_2' => ['nullable', 'string'],
                    'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);
                \Log::info('HeroSection validation - SUCCESS');
            } catch (ValidationException $e) {
                \Log::error('HeroSection validation - FAILED: ' . json_encode($e->errors()));
                throw $e;
            }

            $existingSection = HeroSection::first();

            // Handle image uploads (only if provided)
            $imageFields = [
                'hero_background_image',
                'hero_image',
            ];

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($existingSection && $existingSection->$field) {
                        Storage::disk('public')->delete($existingSection->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('hero-section', 'public');
                    $validated[$field] = $imagePath;
                } else {
                    // Keep existing image if not updating
                    $validated[$field] = $existingSection->$field ?? null;
                }
            }

            // Update or create hero section
            $section = HeroSection::updateOrCreate(
                ['id' => HeroSection::first()?->id ?? 0],
                $validated
            );

            // Broadcast content update
            $this->broadcastContentUpdate('hero-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hero section updated successfully',
                'data' => [
                    'hero_section' => [
                        'id' => $section->id,
                        'hero_background_image' => $section->hero_background_image_url,
                        'title_part_1' => $section->title_part_1,
                        'title_part_2' => $section->title_part_2,
                        'description_1' => $section->description_1,
                        'description_2' => $section->description_2,
                        'hero_image' => $section->hero_image_url,
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
                'message' => 'Hero section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during hero section update.',
            ], 500);
        }
    }

    /**
     * Update hero section content.
     * 
     * Updates the existing hero section configuration.
     * Only super admin and editor can access this.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Check if user has admin access (super_admin or editor)
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage hero section content.',
                ], 403);
            }

            $section = HeroSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hero section not found.',
                ], 404);
            }

            // Get all input data
            $dataToUpdate = [];

            // Text fields
            $textFields = [
                'title_part_1',
                'title_part_2',
                'description_1',
                'description_2',
            ];

            foreach ($textFields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle image uploads and deletion
            $imageFields = [
                'hero_background_image',
                'hero_image',
            ];

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($section->$field) {
                        Storage::disk('public')->delete($section->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('hero-section', 'public');
                    $dataToUpdate[$field] = $imagePath;
                } else {
                    // Check if field should be deleted
                    $fieldValue = $request->input($field);
                    $fieldExists = $request->has($field) || array_key_exists($field, $request->all());
                    
                    if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                        // Delete old image if exists
                        if ($section->$field) {
                            Storage::disk('public')->delete($section->$field);
                        }
                        $dataToUpdate[$field] = null;
                    }
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                // Text field validation rules
                foreach ($textFields as $field) {
                    if (isset($dataToUpdate[$field])) {
                        if ($field === 'description_1' || $field === 'description_2') {
                            $rules[$field] = ['nullable', 'string'];
                        } else {
                            $rules[$field] = ['nullable', 'string', 'max:255'];
                        }
                    }
                }
                
                // Image field validation rules (only if files are uploaded)
                foreach ($imageFields as $field) {
                    if ($request->hasFile($field)) {
                        $rules[$field] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                    }
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('hero-section', 'updated', [
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
                'message' => 'Hero section updated successfully',
                'data' => [
                    'hero_section' => [
                        'id' => $section->id,
                        'hero_background_image' => $section->hero_background_image_url,
                        'title_part_1' => $section->title_part_1,
                        'title_part_2' => $section->title_part_2,
                        'description_1' => $section->description_1,
                        'description_2' => $section->description_2,
                        'hero_image' => $section->hero_image_url,
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
                'message' => 'Hero section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during hero section update.',
            ], 500);
        }
    }

    /**
     * Show a specific hero section by ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $section = HeroSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Hero section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hero_section' => [
                    'id' => $section->id,
                    'hero_background_image' => $section->hero_background_image_url,
                    'title_part_1' => $section->title_part_1,
                    'title_part_2' => $section->title_part_2,
                    'description_1' => $section->description_1,
                    'description_2' => $section->description_2,
                    'hero_image' => $section->hero_image_url,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete hero section content.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Check if user has admin access (super_admin or editor)
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete hero section content.',
                ], 403);
            }

            $section = HeroSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hero section not found.',
                ], 404);
            }

            // Delete associated images
            if ($section->hero_background_image) {
                Storage::disk('public')->delete($section->hero_background_image);
            }
            if ($section->hero_image) {
                Storage::disk('public')->delete($section->hero_image);
            }

            $sectionId = $section->id;
            $section->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('hero-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hero section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hero section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during hero section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from hero section.
     * 
     * @param Request $request
     * @param int $id
     * @param string $field
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteField(Request $request, $id, $field)
    {
        try {
            // Check if user has admin access (super_admin or editor)
            $currentUser = $request->user();
            
            if (!$currentUser->hasAdminAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete hero section fields.',
                ], 403);
            }

            $section = HeroSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hero section not found.',
                ], 404);
            }

            $allowedFields = [
                'hero_background_image',
                'title_part_1',
                'title_part_2',
                'description_1',
                'description_2',
                'hero_image',
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            $imageFields = ['hero_background_image', 'hero_image'];

            if (in_array($field, $imageFields) && $section->$field) {
                Storage::disk('public')->delete($section->$field);
            }

            $section->$field = null;
            $section->save();
            $section->refresh();

            // Broadcast content update
            $this->broadcastContentUpdate('hero-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'hero_section' => [
                        'id' => $section->id,
                        'hero_background_image' => $section->hero_background_image_url,
                        'title_part_1' => $section->title_part_1,
                        'title_part_2' => $section->title_part_2,
                        'description_1' => $section->description_1,
                        'description_2' => $section->description_2,
                        'hero_image' => $section->hero_image_url,
                        'updated_at' => $section->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Field deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during field deletion.',
            ], 500);
        }
    }
}

