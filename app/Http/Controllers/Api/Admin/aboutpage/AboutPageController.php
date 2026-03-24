<?php

namespace App\Http\Controllers\Api\Admin\aboutpage;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AboutPageController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get about page content.
     * 
     * Returns the current about page configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $aboutPage = AboutPage::first();

        if (!$aboutPage) {
            return response()->json([
                'success' => true,
                'data' => [
                    'about_page' => null,
                    'message' => 'About page not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'about_page' => [
                    'id' => $aboutPage->id,
                    // Hero Section
                    'hero_background_image' => $aboutPage->hero_background_image_url,
                    'title_part_1' => $aboutPage->title_part_1,
                    'title_part_2' => $aboutPage->title_part_2,
                    'description_1' => $aboutPage->description_1,
                    'description_2' => $aboutPage->description_2,
                    'hero_image' => $aboutPage->hero_image_url,
                    // About the Founder Section
                    'founder_title' => $aboutPage->founder_title,
                    'founder_description' => $aboutPage->founder_description,
                    // About our Company Section
                    'company_title' => $aboutPage->company_title,
                    'company_description' => $aboutPage->company_description,
                    'company_image' => $aboutPage->company_image_url,
                    // Mission and Vision Section
                    'mission_title' => $aboutPage->mission_title,
                    'vision_title' => $aboutPage->vision_title,
                    'mission_description' => $aboutPage->mission_description,
                    'vision_description' => $aboutPage->vision_description,
                    'created_at' => $aboutPage->created_at,
                    'updated_at' => $aboutPage->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update about page content.
     * 
     * Creates a new about page if none exists, or updates the existing one.
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
                    'message' => 'Unauthorized. Only admins can manage about page content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                // Hero Section
                'hero_background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'title_part_1' => ['nullable', 'string', 'max:255'],
                'title_part_2' => ['nullable', 'string', 'max:255'],
                'description_1' => ['nullable', 'string'],
                'description_2' => ['nullable', 'string'],
                'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                // About the Founder Section
                'founder_title' => ['nullable', 'string', 'max:255'],
                'founder_description' => ['nullable', 'string'],
                // About our Company Section
                'company_title' => ['nullable', 'string', 'max:255'],
                'company_description' => ['nullable', 'string'],
                'company_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                // Mission and Vision Section
                'mission_title' => ['nullable', 'string', 'max:255'],
                'vision_title' => ['nullable', 'string', 'max:255'],
                'mission_description' => ['nullable', 'string'],
                'vision_description' => ['nullable', 'string'],
            ]);

            $existingAboutPage = AboutPage::first();

            // Handle image uploads (only if provided)
            $imageFields = [
                'hero_background_image',
                'hero_image',
                'company_image',
            ];

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($existingAboutPage && $existingAboutPage->$field) {
                        Storage::disk('public')->delete($existingAboutPage->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('about-page', 'public');
                    $validated[$field] = $imagePath;
                } else {
                    // Keep existing image if not updating
                    $validated[$field] = $existingAboutPage->$field ?? null;
                }
            }

            // Update or create about page
            $aboutPage = AboutPage::updateOrCreate(
                ['id' => AboutPage::first()?->id ?? 0],
                $validated
            );

            // Broadcast content update
            $this->broadcastContentUpdate('about-page', 'updated', [
                'id' => $aboutPage->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'About page updated successfully',
                'data' => [
                    'about_page' => [
                        'id' => $aboutPage->id,
                        'hero_background_image' => $aboutPage->hero_background_image_url,
                        'title_part_1' => $aboutPage->title_part_1,
                        'title_part_2' => $aboutPage->title_part_2,
                        'description_1' => $aboutPage->description_1,
                        'description_2' => $aboutPage->description_2,
                        'hero_image' => $aboutPage->hero_image_url,
                        'founder_title' => $aboutPage->founder_title,
                        'founder_description' => $aboutPage->founder_description,
                        'company_title' => $aboutPage->company_title,
                        'company_description' => $aboutPage->company_description,
                        'company_image' => $aboutPage->company_image_url,
                        'mission_title' => $aboutPage->mission_title,
                        'vision_title' => $aboutPage->vision_title,
                        'mission_description' => $aboutPage->mission_description,
                        'vision_description' => $aboutPage->vision_description,
                        'updated_at' => $aboutPage->updated_at,
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
                'message' => 'About page update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about page update.',
            ], 500);
        }
    }

    /**
     * Update about page content.
     * 
     * Updates the existing about page configuration.
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
                    'message' => 'Unauthorized. Only admins can manage about page content.',
                ], 403);
            }

            $aboutPage = AboutPage::find($id);

            if (!$aboutPage) {
                return response()->json([
                    'success' => false,
                    'message' => 'About page not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // Text fields
            $textFields = [
                'title_part_1',
                'title_part_2',
                'description_1',
                'description_2',
                'founder_title',
                'founder_description',
                'company_title',
                'company_description',
                'mission_title',
                'vision_title',
                'mission_description',
                'vision_description',
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
                'hero_background_image',
                'hero_image',
                'company_image',
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
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($aboutPage->$field) {
                        Storage::disk('public')->delete($aboutPage->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('about-page', 'public');
                    $dataToUpdate[$field] = $imagePath;
                } else {
                    // Check if field should be deleted (null or "delete" string)
                    $fieldValue = $request->input($field);
                    $fieldExists = $request->has($field) || array_key_exists($field, $request->all());
                    
                    if ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                        // Delete old image if exists
                        if ($aboutPage->$field) {
                            Storage::disk('public')->delete($aboutPage->$field);
                        }
                        $dataToUpdate[$field] = null;
                    }
                }
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                // Validate text fields if being updated
                foreach ($textFields as $field) {
                    if (isset($dataToUpdate[$field])) {
                        $rules[$field] = ['nullable', 'string', 'max:255'];
                    }
                }
                
                // Validate description fields
                $descriptionFields = ['description_1', 'description_2', 'founder_description', 'company_description', 'mission_description', 'vision_description'];
                foreach ($descriptionFields as $field) {
                    if (isset($dataToUpdate[$field])) {
                        $rules[$field] = ['nullable', 'string'];
                    }
                }

                // Validate
                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $aboutPage->fill($dataToUpdate);
                $aboutPage->save();
                $aboutPage->refresh();

                // Broadcast content update
                $this->broadcastContentUpdate('about-page', 'updated', [
                    'id' => $aboutPage->id,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'About page updated successfully',
                'data' => [
                    'about_page' => [
                        'id' => $aboutPage->id,
                        'hero_background_image' => $aboutPage->hero_background_image_url,
                        'title_part_1' => $aboutPage->title_part_1,
                        'title_part_2' => $aboutPage->title_part_2,
                        'description_1' => $aboutPage->description_1,
                        'description_2' => $aboutPage->description_2,
                        'hero_image' => $aboutPage->hero_image_url,
                        'founder_title' => $aboutPage->founder_title,
                        'founder_description' => $aboutPage->founder_description,
                        'company_title' => $aboutPage->company_title,
                        'company_description' => $aboutPage->company_description,
                        'company_image' => $aboutPage->company_image_url,
                        'mission_title' => $aboutPage->mission_title,
                        'vision_title' => $aboutPage->vision_title,
                        'mission_description' => $aboutPage->mission_description,
                        'vision_description' => $aboutPage->vision_description,
                        'updated_at' => $aboutPage->updated_at,
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
                'message' => 'About page update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about page update.',
            ], 500);
        }
    }

    /**
     * Show a specific about page by ID.
     * 
     * Returns the about page configuration for a specific ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $aboutPage = AboutPage::find($id);

        if (!$aboutPage) {
            return response()->json([
                'success' => false,
                'message' => 'About page not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'about_page' => [
                    'id' => $aboutPage->id,
                    'hero_background_image' => $aboutPage->hero_background_image_url,
                    'title_part_1' => $aboutPage->title_part_1,
                    'title_part_2' => $aboutPage->title_part_2,
                    'description_1' => $aboutPage->description_1,
                    'description_2' => $aboutPage->description_2,
                    'hero_image' => $aboutPage->hero_image_url,
                    'founder_title' => $aboutPage->founder_title,
                    'founder_description' => $aboutPage->founder_description,
                    'company_title' => $aboutPage->company_title,
                    'company_description' => $aboutPage->company_description,
                    'company_image' => $aboutPage->company_image_url,
                    'mission_title' => $aboutPage->mission_title,
                    'vision_title' => $aboutPage->vision_title,
                    'mission_description' => $aboutPage->mission_description,
                    'vision_description' => $aboutPage->vision_description,
                    'created_at' => $aboutPage->created_at,
                    'updated_at' => $aboutPage->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete about page content.
     * 
     * Deletes a specific about page record and its associated images.
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
                    'message' => 'Unauthorized. Only admins can delete about page content.',
                ], 403);
            }

            $aboutPage = AboutPage::find($id);

            if (!$aboutPage) {
                return response()->json([
                    'success' => false,
                    'message' => 'About page not found.',
                ], 404);
            }

            // Delete associated images from storage
            $imageFields = [
                'hero_background_image',
                'hero_image',
                'company_image',
            ];

            foreach ($imageFields as $field) {
                if ($aboutPage->$field) {
                    Storage::disk('public')->delete($aboutPage->$field);
                }
            }

            $aboutPageId = $aboutPage->id;
            
            // Delete the about page record
            $aboutPage->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('about-page', 'deleted', [
                'id' => $aboutPageId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'About page deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'About page deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during about page deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from about page.
     * 
     * Deletes a single field (e.g., an image) from the about page without deleting the entire page.
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
                    'message' => 'Unauthorized. Only admins can delete about page fields.',
                ], 403);
            }

            $aboutPage = AboutPage::find($id);

            if (!$aboutPage) {
                return response()->json([
                    'success' => false,
                    'message' => 'About page not found.',
                ], 404);
            }

            // List of all allowed fields
            $allowedFields = [
                'hero_background_image',
                'title_part_1',
                'title_part_2',
                'description_1',
                'description_2',
                'hero_image',
                'founder_title',
                'founder_description',
                'company_title',
                'company_description',
                'company_image',
                'mission_title',
                'vision_title',
                'mission_description',
                'vision_description',
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
                'hero_background_image',
                'hero_image',
                'company_image',
            ];

            // Delete image file if it's an image field
            if (in_array($field, $imageFields) && $aboutPage->$field) {
                Storage::disk('public')->delete($aboutPage->$field);
            }

            // Set field to null in database
            $aboutPage->$field = null;
            $aboutPage->save();
            $aboutPage->refresh();

            // Broadcast content update
            $this->broadcastContentUpdate('about-page', 'updated', [
                'id' => $aboutPage->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'about_page' => [
                        'id' => $aboutPage->id,
                        'hero_background_image' => $aboutPage->hero_background_image_url,
                        'title_part_1' => $aboutPage->title_part_1,
                        'title_part_2' => $aboutPage->title_part_2,
                        'description_1' => $aboutPage->description_1,
                        'description_2' => $aboutPage->description_2,
                        'hero_image' => $aboutPage->hero_image_url,
                        'founder_title' => $aboutPage->founder_title,
                        'founder_description' => $aboutPage->founder_description,
                        'company_title' => $aboutPage->company_title,
                        'company_description' => $aboutPage->company_description,
                        'company_image' => $aboutPage->company_image_url,
                        'mission_title' => $aboutPage->mission_title,
                        'vision_title' => $aboutPage->vision_title,
                        'mission_description' => $aboutPage->mission_description,
                        'vision_description' => $aboutPage->vision_description,
                        'updated_at' => $aboutPage->updated_at,
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

