<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QuoteSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get quote section content.
     * 
     * Returns the current quote section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = QuoteSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'quote_section' => null,
                    'message' => 'Quote section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'quote_section' => [
                    'id' => $section->id,
                    'hero_title' => $section->hero_title,
                    'request_quote_title' => $section->request_quote_title,
                    'request_quote_subtitle' => $section->request_quote_subtitle,
                    'description' => $section->description,
                    'button_text' => $section->button_text,
                    'title_1' => $section->title_1,
                    'paragraph_1' => $section->paragraph_1,
                    'title_2' => $section->title_2,
                    'paragraph_2' => $section->paragraph_2,
                    'image_1' => $section->image_1_url,
                    'image_2' => $section->image_2_url,
                    'background_image' => $section->background_image_url,
                    'card_1_color' => $section->card_1_color,
                    'card_2_color' => $section->card_2_color,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update quote section content.
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
                    'message' => 'Unauthorized. Only admins can manage quote section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'hero_title' => ['nullable', 'string', 'max:255'],
                'request_quote_title' => ['nullable', 'string', 'max:255'],
                'request_quote_subtitle' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'title_1' => ['nullable', 'string', 'max:255'],
                'paragraph_1' => ['nullable', 'string'],
                'title_2' => ['nullable', 'string', 'max:255'],
                'paragraph_2' => ['nullable', 'string'],
                'image_1' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'image_2' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'card_1_color' => ['nullable', 'string', 'max:20'],
                'card_2_color' => ['nullable', 'string', 'max:20'],
            ]);

            $existingSection = QuoteSection::first();

            // Handle image uploads
            $imageFields = ['image_1', 'image_2', 'background_image'];
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($existingSection && $existingSection->$field) {
                        Storage::disk('public')->delete($existingSection->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('quote-section', 'public');
                    $validated[$field] = $imagePath;
                } else {
                    // Keep existing image if not updating
                    $validated[$field] = $existingSection->$field ?? null;
                }
            }

            // Update or create section
            $section = QuoteSection::updateOrCreate(
                ['id' => QuoteSection::first()?->id ?? 0],
                $validated
            );

            // Broadcast content update
            $this->broadcastContentUpdate('quote-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quote section updated successfully',
                'data' => [
                    'quote_section' => [
                        'id' => $section->id,
                        'hero_title' => $section->hero_title,
                        'request_quote_title' => $section->request_quote_title,
                        'request_quote_subtitle' => $section->request_quote_subtitle,
                        'description' => $section->description,
                        'button_text' => $section->button_text,
                        'title_1' => $section->title_1,
                        'paragraph_1' => $section->paragraph_1,
                        'title_2' => $section->title_2,
                        'paragraph_2' => $section->paragraph_2,
                        'image_1' => $section->image_1_url,
                        'image_2' => $section->image_2_url,
                        'background_image' => $section->background_image_url,
                        'card_1_color' => $section->card_1_color,
                        'card_2_color' => $section->card_2_color,
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
                'message' => 'Quote section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during quote section update.',
            ], 500);
        }
    }

    /**
     * Update quote section content.
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
                    'message' => 'Unauthorized. Only admins can manage quote section content.',
                ], 403);
            }

            // For singleton sections, try to find by ID first, then fall back to first()
            $section = QuoteSection::find($id);
            
            // If not found by ID and this is a singleton section, try to get the first one
            if (!$section) {
                $section = QuoteSection::first();
                
                if (!$section) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quote section not found. Please create a quote section first using POST /api/v1/quote-section.',
                    ], 404);
                }
                
                // For singleton sections, automatically use the existing section if wrong ID provided
                // This makes the API more user-friendly since there's only one section anyway
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
            $textFields = [
                'hero_title',
                'request_quote_title',
                'request_quote_subtitle',
                'description',
                'button_text',
                'title_1',
                'paragraph_1',
                'title_2',
                'paragraph_2',
                'card_1_color',
                'card_2_color',
            ];

            foreach ($textFields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field) || array_key_exists($field, $request->all())) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle image uploads and deletion
            $imageFields = ['image_1', 'image_2', 'background_image'];
            
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
                    $maxSize = $field === 'background_image' ? 5120 : 2048;
                    $validationRules[$field] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', "max:$maxSize"];
                }
                $request->validate($validationRules);
            }

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Verify file was uploaded successfully
                    if (!$request->file($field)->isValid()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Image upload failed. File may be too large or corrupted.',
                            'errors' => [
                                $field => ['The image file could not be uploaded. Please check file size (max 15MB) and try again.']
                            ],
                        ], 422);
                    }

                    // Delete old image if exists
                    if ($section->$field) {
                        Storage::disk('public')->delete($section->$field);
                    }

                    try {
                        // Store new image
                        $imagePath = $request->file($field)->store('quote-section', 'public');
                        $dataToUpdate[$field] = $imagePath;
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Image upload failed.',
                            'errors' => [
                                $field => [config('app.debug') ? $e->getMessage() : 'The image failed to upload. Please check file size (max 15MB) and try again.']
                            ],
                        ], 422);
                    }
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
                
                foreach ($textFields as $field) {
                    if (isset($dataToUpdate[$field])) {
                        if (in_array($field, ['description', 'paragraph_1', 'paragraph_2'])) {
                            $rules[$field] = ['nullable', 'string'];
                        } else {
                            $rules[$field] = ['nullable', 'string', 'max:255'];
                        }
                    }
                }
                
                foreach ($imageFields as $field) {
                    if (isset($dataToUpdate[$field]) && $request->hasFile($field)) {
                        $rules[$field] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
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
                $this->broadcastContentUpdate('quote-section', 'updated', [
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
                'message' => 'Quote section updated successfully',
                'data' => [
                    'quote_section' => [
                        'id' => $section->id,
                        'hero_title' => $section->hero_title,
                        'request_quote_title' => $section->request_quote_title,
                        'request_quote_subtitle' => $section->request_quote_subtitle,
                        'description' => $section->description,
                        'button_text' => $section->button_text,
                        'title_1' => $section->title_1,
                        'paragraph_1' => $section->paragraph_1,
                        'title_2' => $section->title_2,
                        'paragraph_2' => $section->paragraph_2,
                        'image_1' => $section->image_1_url,
                        'image_2' => $section->image_2_url,
                        'background_image' => $section->background_image_url,
                        'card_1_color' => $section->card_1_color,
                        'card_2_color' => $section->card_2_color,
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
                'message' => 'Quote section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during quote section update.',
            ], 500);
        }
    }

    /**
     * Delete quote section content.
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
                    'message' => 'Unauthorized. Only admins can delete quote section content.',
                ], 403);
            }

            // For singleton sections, try to find by ID first, then fall back to first()
            $section = QuoteSection::find($id);
            
            // If not found by ID and this is a singleton section, try to get the first one
            if (!$section) {
                $section = QuoteSection::first();
                
                if (!$section) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quote section not found. No quote section exists to delete.',
                    ], 404);
                }
                
                // For singleton sections, automatically use the existing section if wrong ID provided
            }

            // Delete associated images from storage
            $imageFields = ['image_1', 'image_2', 'background_image'];
            foreach ($imageFields as $field) {
                if ($section->$field) {
                    Storage::disk('public')->delete($section->$field);
                }
            }

            // Delete the section record
            $sectionId = $section->id;
            $section->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('quote-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quote section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quote section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during quote section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from quote section.
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
                    'message' => 'Unauthorized. Only admins can delete quote section fields.',
                ], 403);
            }

            // For singleton sections, try to find by ID first, then fall back to first()
            $section = QuoteSection::find($id);
            
            // If not found by ID and this is a singleton section, try to get the first one
            if (!$section) {
                $section = QuoteSection::first();
                
                if (!$section) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quote section not found. No quote section exists.',
                    ], 404);
                }
                
                // For singleton sections, automatically use the existing section if wrong ID provided
            }

            // List of all allowed fields
            $allowedFields = [
                'image_1',
                'image_2',
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
            if (in_array($field, $allowedFields) && $section->$field) {
                Storage::disk('public')->delete($section->$field);
            }

            // Set field to null in database
            $section->$field = null;
            $section->save();
            $section->refresh();

            // Broadcast content update
            $this->broadcastContentUpdate('quote-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'quote_section' => [
                        'id' => $section->id,
                        'hero_title' => $section->hero_title,
                        'request_quote_title' => $section->request_quote_title,
                        'request_quote_subtitle' => $section->request_quote_subtitle,
                        'description' => $section->description,
                        'button_text' => $section->button_text,
                        'title_1' => $section->title_1,
                        'paragraph_1' => $section->paragraph_1,
                        'title_2' => $section->title_2,
                        'paragraph_2' => $section->paragraph_2,
                        'image_1' => $section->image_1_url,
                        'image_2' => $section->image_2_url,
                        'background_image' => $section->background_image_url,
                        'card_1_color' => $section->card_1_color,
                        'card_2_color' => $section->card_2_color,
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
