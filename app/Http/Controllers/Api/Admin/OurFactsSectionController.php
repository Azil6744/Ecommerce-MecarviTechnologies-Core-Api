<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OurFactsSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OurFactsSectionController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get our facts section content.
     * 
     * Returns the current our facts section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = OurFactsSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'our_facts_section' => null,
                    'message' => 'Our facts section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'our_facts_section' => [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'small_description' => $section->small_description,
                    'large_number' => $section->large_number,
                    'large_number_image' => $section->large_number_image_url,
                    'background_image' => $section->background_image_url,
                    'background_color' => $section->background_color,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update our facts section content.
     * 
     * Creates a new section if none exists, or updates the existing one.
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
                    'message' => 'Unauthorized. Only admins can manage our facts section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'section_title' => ['nullable', 'string', 'max:255'],
                'small_description' => ['nullable', 'string'],
                'large_number' => ['nullable', 'string', 'max:255'],
                'large_number_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_color' => ['nullable', 'string', 'max:50'],
            ]);

            $existingSection = OurFactsSection::first();

            // Handle background image upload (only if provided)
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($existingSection && $existingSection->background_image) {
                    Storage::disk('public')->delete($existingSection->background_image);
                }

                // Store new image
                $imagePath = $request->file('background_image')->store('our-facts-section', 'public');
                $validated['background_image'] = $imagePath;
            } else {
                $validated['background_image'] = $existingSection->background_image ?? null;
            }
            if (!isset($validated['background_color'])) {
                $validated['background_color'] = $existingSection->background_color ?? null;
            }

            // Handle large number image upload (only if provided)
            if ($request->hasFile('large_number_image')) {
                // Delete old image if exists
                if ($existingSection && $existingSection->large_number_image) {
                    Storage::disk('public')->delete($existingSection->large_number_image);
                }

                // Store new image
                $imagePath = $request->file('large_number_image')->store('our-facts-section', 'public');
                $validated['large_number_image'] = $imagePath;
            } else {
                // Keep existing image if not updating
                $validated['large_number_image'] = $existingSection->large_number_image ?? null;
            }

            // Update or create section
            $section = OurFactsSection::updateOrCreate(
                ['id' => OurFactsSection::first()?->id ?? 0],
                $validated
            );

            // Broadcast content update
            $this->broadcastContentUpdate('our-facts-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Our facts section updated successfully',
                'data' => [
                    'our_facts_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'small_description' => $section->small_description,
                        'large_number' => $section->large_number,
                        'large_number_image' => $section->large_number_image_url,
                        'background_image' => $section->background_image_url,
                        'background_color' => $section->background_color,
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
                'message' => 'Our facts section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our facts section update.',
            ], 500);
        }
    }

    /**
     * Update our facts section content.
     * 
     * Updates the existing section configuration.
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
                    'message' => 'Unauthorized. Only admins can manage our facts section content.',
                ], 403);
            }

            $section = OurFactsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Our facts section not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data
            $isMultipart = $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data');
            
            if ($isMultipart) {
                $allInput = $request->all();
                if (!empty($allInput)) {
                    $request->merge($allInput);
                }
            }

            // Handle text fields
            if ($request->filled('section_title')) {
                $dataToUpdate['section_title'] = $request->input('section_title');
            } elseif ($request->has('section_title') || array_key_exists('section_title', $request->all())) {
                $dataToUpdate['section_title'] = $request->input('section_title');
            }

            if ($request->filled('large_number')) {
                $dataToUpdate['large_number'] = $request->input('large_number');
            } elseif ($request->has('large_number') || array_key_exists('large_number', $request->all())) {
                $dataToUpdate['large_number'] = $request->input('large_number');
            }

            if ($request->filled('small_description')) {
                $dataToUpdate['small_description'] = $request->input('small_description');
            } elseif ($request->has('small_description') || array_key_exists('small_description', $request->all())) {
                $dataToUpdate['small_description'] = $request->input('small_description');
            }
            if ($request->has('background_color')) {
                $request->validate([
                    'background_color' => ['nullable', 'string', 'max:50'],
                ]);
            }
            if ($request->filled('background_color')) {
                $dataToUpdate['background_color'] = $request->input('background_color');
            } elseif ($request->has('background_color') || array_key_exists('background_color', $request->all())) {
                $dataToUpdate['background_color'] = $request->input('background_color');
            }

            // Handle background image upload and deletion
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($section->background_image) {
                    Storage::disk('public')->delete($section->background_image);
                }

                // Store new image
                $imagePath = $request->file('background_image')->store('our-facts-section', 'public');
                $dataToUpdate['background_image'] = $imagePath;
            } else {
                // Check if field should be deleted
                $fieldValue = $request->input('background_image');
                $fieldExists = $request->has('background_image') || array_key_exists('background_image', $request->all());
                
                if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                    // Delete old image if exists
                    if ($section->background_image) {
                        Storage::disk('public')->delete($section->background_image);
                    }
                    $dataToUpdate['background_image'] = null;
                }
            }

            // Handle large number image upload and deletion
            if ($request->hasFile('large_number_image')) {
                // Delete old image if exists
                if ($section->large_number_image) {
                    Storage::disk('public')->delete($section->large_number_image);
                }

                // Store new image
                $imagePath = $request->file('large_number_image')->store('our-facts-section', 'public');
                $dataToUpdate['large_number_image'] = $imagePath;
            } else {
                // Check if field should be deleted
                $fieldValue = $request->input('large_number_image');
                $fieldExists = $request->has('large_number_image') || array_key_exists('large_number_image', $request->all());
                
                if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                    // Delete old image if exists
                    if ($section->large_number_image) {
                        Storage::disk('public')->delete($section->large_number_image);
                    }
                    $dataToUpdate['large_number_image'] = null;
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['section_title'])) {
                    $rules['section_title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['large_number'])) {
                    $rules['large_number'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['small_description'])) {
                    $rules['small_description'] = ['nullable', 'string'];
                }
                if (isset($dataToUpdate['background_image']) && $request->hasFile('background_image')) {
                    $rules['background_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }
                if (isset($dataToUpdate['large_number_image']) && $request->hasFile('large_number_image')) {
                    $rules['large_number_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('our-facts-section', 'updated', [
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
                'message' => 'Our facts section updated successfully',
                'data' => [
                    'our_facts_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'small_description' => $section->small_description,
                        'large_number' => $section->large_number,
                        'large_number_image' => $section->large_number_image_url,
                        'background_image' => $section->background_image_url,
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
                'message' => 'Our facts section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our facts section update.',
            ], 500);
        }
    }

    /**
     * Delete our facts section content.
     * 
     * Deletes the section and associated images.
     * Only super admin and editor can access this.
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
                    'message' => 'Unauthorized. Only admins can delete our facts section content.',
                ], 403);
            }

            $section = OurFactsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Our facts section not found.',
                ], 404);
            }

            // Delete associated image from storage
            if ($section->background_image) {
                Storage::disk('public')->delete($section->background_image);
            }
            if ($section->large_number_image) {
                Storage::disk('public')->delete($section->large_number_image);
            }

            // Delete the section record
            $sectionId = $section->id;
            $section->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('our-facts-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Our facts section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Our facts section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during our facts section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from our facts section.
     * 
     * Deletes a single field (e.g., an image) from the section without deleting the entire section.
     * Only super admin and editor can access this.
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
                    'message' => 'Unauthorized. Only admins can delete our facts section fields.',
                ], 403);
            }

            $section = OurFactsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Our facts section not found.',
                ], 404);
            }

            // List of all allowed fields (setting to null)
            $allowedFields = [
                'section_title',
                'small_description',
                'large_number',
                'large_number_image',
                'background_image',
            ];

            // Validate field name
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            // Delete image file if it's an image field
            if ($field === 'background_image' && $section->background_image) {
                Storage::disk('public')->delete($section->background_image);
            }
            if ($field === 'large_number_image' && $section->large_number_image) {
                Storage::disk('public')->delete($section->large_number_image);
            }

            // Set field to null in database
            $section->$field = null;
            $section->save();
            $section->refresh();

            // Broadcast content update
            $this->broadcastContentUpdate('our-facts-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'our_facts_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'small_description' => $section->small_description,
                        'large_number' => $section->large_number,
                        'large_number_image' => $section->large_number_image_url,
                        'background_image' => $section->background_image_url,
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
