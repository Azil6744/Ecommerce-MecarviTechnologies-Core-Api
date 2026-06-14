<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AboutSectionController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get about section content.
     * 
     * Returns the current about section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $aboutSection = AboutSection::first();

        if (!$aboutSection) {
            return response()->json([
                'success' => true,
                'data' => [
                    'about_section' => null,
                    'message' => 'About section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'about_section' => [
                    'id' => $aboutSection->id,
                    'main_title' => $aboutSection->main_title,
                    'main_description' => $aboutSection->main_description,
                    'background_image' => $aboutSection->background_image_url,
                    'tab1_title' => $aboutSection->tab1_title,
                    'tab1_subtitle' => $aboutSection->tab1_subtitle,
                    'tab1_description' => $aboutSection->tab1_description,
                    'tab1_image' => $aboutSection->tab1_image_url,
                    'tab2_title' => $aboutSection->tab2_title,
                    'tab2_subtitle' => $aboutSection->tab2_subtitle,
                    'tab2_description' => $aboutSection->tab2_description,
                    'tab2_image' => $aboutSection->tab2_image_url,
                    'about_image_1' => $aboutSection->about_image_1_url,
                    'about_image_2' => $aboutSection->about_image_2_url,
                    'created_at' => $aboutSection->created_at,
                    'updated_at' => $aboutSection->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update about section content.
     * 
     * Creates a new about section if none exists, or updates the existing one.
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
                    'message' => 'Unauthorized. Only admins can manage about section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'main_title' => ['nullable', 'string', 'max:255'],
                'main_description' => ['nullable', 'string'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'tab1_title' => ['nullable', 'string', 'max:255'],
                'tab1_subtitle' => ['nullable', 'string', 'max:255'],
                'tab1_description' => ['nullable', 'string'],
                'tab1_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'tab2_title' => ['nullable', 'string', 'max:255'],
                'tab2_subtitle' => ['nullable', 'string', 'max:255'],
                'tab2_description' => ['nullable', 'string'],
                'tab2_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'about_image_1' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'about_image_2' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            ]);

            $existingAboutSection = AboutSection::first();

            // Handle image uploads (only if provided)
            $imageFields = [
                'background_image',
                'tab1_image',
                'tab2_image',
                'about_image_1',
                'about_image_2',
            ];

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($existingAboutSection && $existingAboutSection->$field) {
                        Storage::disk('public')->delete($existingAboutSection->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('about-section', 'public');
                    $validated[$field] = $imagePath;
                } else {
                    // Keep existing image if not updating
                    $validated[$field] = $existingAboutSection->$field ?? null;
                }
            }

            // Update or create about section
            $aboutSection = AboutSection::updateOrCreate(
                ['id' => AboutSection::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('about-section', 'updated', [
                'id' => $aboutSection->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'About section updated successfully',
                'data' => [
                    'about_section' => [
                        'id' => $aboutSection->id,
                        'main_title' => $aboutSection->main_title,
                        'main_description' => $aboutSection->main_description,
                        'background_image' => $aboutSection->background_image_url,
                        'tab1_title' => $aboutSection->tab1_title,
                        'tab1_subtitle' => $aboutSection->tab1_subtitle,
                        'tab1_description' => $aboutSection->tab1_description,
                        'tab1_image' => $aboutSection->tab1_image_url,
                        'tab2_title' => $aboutSection->tab2_title,
                        'tab2_subtitle' => $aboutSection->tab2_subtitle,
                        'tab2_description' => $aboutSection->tab2_description,
                        'tab2_image' => $aboutSection->tab2_image_url,
                        'about_image_1' => $aboutSection->about_image_1_url,
                        'about_image_2' => $aboutSection->about_image_2_url,
                        'updated_at' => $aboutSection->updated_at,
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
                'message' => 'About section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about section update.',
            ], 500);
        }
    }

    /**
     * Update about section content.
     * 
     * Updates the existing about section configuration.
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
                    'message' => 'Unauthorized. Only admins can manage about section content.',
                ], 403);
            }

            $aboutSection = AboutSection::find($id);

            if (!$aboutSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'About section not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data, Laravel may not parse it automatically
            // Check if this is a multipart/form-data request (file upload)
            $isMultipart = $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data');
            
            if ($isMultipart) {
                // For form-data, ensure request data is properly parsed
                // Files are handled separately via hasFile() which works with multipart
                // But we need to ensure form fields are accessible
                $allInput = $request->all();
                if (!empty($allInput)) {
                    // Merge form data into request for easier access
                    $request->merge($allInput);
                }
            }
            
            // Text fields
            $textFields = [
                'main_title',
                'main_description',
                'tab1_title',
                'tab1_subtitle',
                'tab1_description',
                'tab2_title',
                'tab2_subtitle',
                'tab2_description',
            ];

            foreach ($textFields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field)) {
                    // Allow null to delete field
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle image uploads (only if provided) and deletion
            $imageFields = [
                'background_image',
                'tab1_image',
                'tab2_image',
                'about_image_1',
                'about_image_2',
            ];

            // Collect all files that need validation
            $filesToValidate = [];
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    $filesToValidate[$field] = $request->file($field);
                }
            }
            
            // Validate all files at once if any files are present
            if (!empty($filesToValidate)) {
                $validationRules = [];
                foreach (array_keys($filesToValidate) as $field) {
                    $validationRules[$field] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }
                $request->validate($validationRules);
            }

            foreach ($imageFields as $field) {
                // Check for file upload first (hasFile() works with multipart/form-data)
                if ($request->hasFile($field)) {
                    // File already validated above, so we can safely process it
                    // Delete old image if exists
                    if ($aboutSection->$field) {
                        Storage::disk('public')->delete($aboutSection->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('about-section', 'public');
                    $dataToUpdate[$field] = $imagePath;
                } else {
                    // Check if field should be deleted (null or "delete" string)
                    // For JSON requests, check if field exists in request (even if null)
                    $fieldValue = $request->input($field);
                    $fieldExists = $request->has($field) || array_key_exists($field, $request->all());
                    
                    if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                        // Delete old image if exists
                        if ($aboutSection->$field) {
                            Storage::disk('public')->delete($aboutSection->$field);
                        }
                        $dataToUpdate[$field] = null;
                    }
                }
                // Note: If image is not provided and not deleted, existing image is preserved
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                // Validate text fields if being updated
                if (isset($dataToUpdate['main_title'])) {
                    $rules['main_title'] = ['required', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['tab1_title'])) {
                    $rules['tab1_title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['tab1_subtitle'])) {
                    $rules['tab1_subtitle'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['tab2_title'])) {
                    $rules['tab2_title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['tab2_subtitle'])) {
                    $rules['tab2_subtitle'] = ['nullable', 'string', 'max:255'];
                }

                // Validate image fields if being updated (but not if they're files, as files are validated above)
                $imageFields = ['background_image', 'tab1_image', 'tab2_image', 'about_image_1', 'about_image_2'];
                foreach ($imageFields as $field) {
                    if (isset($dataToUpdate[$field]) && !$request->hasFile($field)) {
                        // Only validate if it's not a file upload (i.e., it's being set to null for deletion)
                        // No additional validation needed for null values
                    }
                }

                // Validate
                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $aboutSection->fill($dataToUpdate);
                $aboutSection->save();
                $aboutSection->refresh();

                $this->broadcastContentUpdate('about-section', 'updated', [
                    'id' => $aboutSection->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'About section updated successfully',
                'data' => [
                    'about_section' => [
                        'id' => $aboutSection->id,
                        'main_title' => $aboutSection->main_title,
                        'main_description' => $aboutSection->main_description,
                        'background_image' => $aboutSection->background_image_url,
                        'tab1_title' => $aboutSection->tab1_title,
                        'tab1_subtitle' => $aboutSection->tab1_subtitle,
                        'tab1_description' => $aboutSection->tab1_description,
                        'tab1_image' => $aboutSection->tab1_image_url,
                        'tab2_title' => $aboutSection->tab2_title,
                        'tab2_subtitle' => $aboutSection->tab2_subtitle,
                        'tab2_description' => $aboutSection->tab2_description,
                        'tab2_image' => $aboutSection->tab2_image_url,
                        'about_image_1' => $aboutSection->about_image_1_url,
                        'about_image_2' => $aboutSection->about_image_2_url,
                        'updated_at' => $aboutSection->updated_at,
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
                'message' => 'About section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about section update.',
            ], 500);
        }
    }

    /**
     * Delete about section content.
     * 
     * Deletes the about section and all associated images.
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
                    'message' => 'Unauthorized. Only admins can delete about section content.',
                ], 403);
            }

            $aboutSection = AboutSection::find($id);

            if (!$aboutSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'About section not found.',
                ], 404);
            }

            // Delete all associated images from storage
            $imageFields = [
                'background_image',
                'tab1_image',
                'tab2_image',
                'about_image_1',
                'about_image_2',
            ];

            foreach ($imageFields as $field) {
                if ($aboutSection->$field) {
                    Storage::disk('public')->delete($aboutSection->$field);
                }
            }

            // Delete the about section record
            $aboutSection->delete();

            $this->broadcastContentUpdate('about-section', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'About section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'About section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from about section.
     * 
     * Deletes a single field (e.g., an image) from the about section without deleting the entire section.
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
                    'message' => 'Unauthorized. Only admins can delete about section fields.',
                ], 403);
            }

            $aboutSection = AboutSection::find($id);

            if (!$aboutSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'About section not found.',
                ], 404);
            }

            // List of all allowed fields
            $allowedFields = [
                'main_title',
                'main_description',
                'background_image',
                'tab1_title',
                'tab1_subtitle',
                'tab1_description',
                'tab1_image',
                'tab2_title',
                'tab2_subtitle',
                'tab2_description',
                'tab2_image',
                'about_image_1',
                'about_image_2',
            ];

            // Validate field name
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            // List of image fields that need file deletion
            $imageFields = [
                'background_image',
                'tab1_image',
                'tab2_image',
                'about_image_1',
                'about_image_2',
            ];

            // Delete image file if it's an image field
            if (in_array($field, $imageFields) && $aboutSection->$field) {
                Storage::disk('public')->delete($aboutSection->$field);
            }

            // Set field to null in database
            $aboutSection->$field = null;
            $aboutSection->save();
            $aboutSection->refresh();

            $this->broadcastContentUpdate('about-section', 'updated', [
                'id' => $aboutSection->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'about_section' => [
                        'id' => $aboutSection->id,
                        'main_title' => $aboutSection->main_title,
                        'main_description' => $aboutSection->main_description,
                        'background_image' => $aboutSection->background_image_url,
                        'tab1_title' => $aboutSection->tab1_title,
                        'tab1_subtitle' => $aboutSection->tab1_subtitle,
                        'tab1_description' => $aboutSection->tab1_description,
                        'tab1_image' => $aboutSection->tab1_image_url,
                        'tab2_title' => $aboutSection->tab2_title,
                        'tab2_subtitle' => $aboutSection->tab2_subtitle,
                        'tab2_description' => $aboutSection->tab2_description,
                        'tab2_image' => $aboutSection->tab2_image_url,
                        'about_image_1' => $aboutSection->about_image_1_url,
                        'about_image_2' => $aboutSection->about_image_2_url,
                        'updated_at' => $aboutSection->updated_at,
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
