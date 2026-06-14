<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewsSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReviewsSectionController extends Controller
{
    use BroadcastsContentUpdates;

    /**
     * Get reviews section content.
     * 
     * Returns the current reviews section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = ReviewsSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'reviews_section' => null,
                    'message' => 'Reviews section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reviews_section' => [
                    'id' => $section->id,
                    'main_heading' => $section->main_heading,
                    'average_rating' => $section->average_rating,
                    'call_to_action_text' => $section->call_to_action_text,
                    'client_label' => $section->client_label,
                    'review_count' => $section->review_count,
                    'button_text' => $section->button_text,
                    'button_url' => $section->button_url,
                    'avatar_1' => $section->avatar_1_url,
                    'avatar_2' => $section->avatar_2_url,
                    'avatar_3' => $section->avatar_3_url,
                    'avatar_4' => $section->avatar_4_url,
                    'background_color' => $section->background_color,
                    'card_background_color' => $section->card_background_color,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update reviews section content.
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
                    'message' => 'Unauthorized. Only admins can manage reviews section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'main_heading' => ['nullable', 'string', 'max:255'],
                'average_rating' => ['nullable', 'string', 'max:255'],
                'call_to_action_text' => ['nullable', 'string'],
                'client_label' => ['nullable', 'string', 'max:255'],
                'review_count' => ['nullable', 'string', 'max:255'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'button_url' => ['nullable', 'string', 'max:500'],
                'background_color' => ['nullable', 'string', 'max:255'],
                'card_background_color' => ['nullable', 'string', 'max:255'],
                'avatar_1' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'avatar_2' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'avatar_3' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'avatar_4' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            ]);

            $existingSection = ReviewsSection::first();

            // Handle avatar image uploads
            $avatarFields = ['avatar_1', 'avatar_2', 'avatar_3', 'avatar_4'];
            foreach ($avatarFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($existingSection && $existingSection->$field) {
                        Storage::disk('public')->delete($existingSection->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('reviews-section', 'public');
                    $validated[$field] = $imagePath;
                } else {
                    // Keep existing image if not updating
                    $validated[$field] = $existingSection->$field ?? null;
                }
            }

            // Update or create section
            $section = ReviewsSection::updateOrCreate(
                ['id' => ReviewsSection::first()?->id ?? 0],
                $validated
            );

            // Broadcast content update
            $this->broadcastContentUpdate('reviews-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reviews section updated successfully',
                'data' => [
                    'reviews_section' => [
                        'id' => $section->id,
                        'main_heading' => $section->main_heading,
                        'average_rating' => $section->average_rating,
                        'call_to_action_text' => $section->call_to_action_text,
                        'client_label' => $section->client_label,
                        'review_count' => $section->review_count,
                        'button_text' => $section->button_text,
                        'button_url' => $section->button_url,
                        'avatar_1' => $section->avatar_1_url,
                        'avatar_2' => $section->avatar_2_url,
                        'avatar_3' => $section->avatar_3_url,
                        'avatar_4' => $section->avatar_4_url,
                        'background_color' => $section->background_color,
                        'card_background_color' => $section->card_background_color,
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
                'message' => 'Reviews section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during reviews section update.',
            ], 500);
        }
    }

    /**
     * Update reviews section content.
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
                    'message' => 'Unauthorized. Only admins can manage reviews section content.',
                ], 403);
            }

            $section = ReviewsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reviews section not found.',
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
            $textFields = ['main_heading', 'average_rating', 'call_to_action_text', 'client_label', 'review_count', 'button_text', 'button_url', 'background_color', 'card_background_color'];
            foreach ($textFields as $field) {
                if ($request->filled($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                } elseif ($request->has($field) || array_key_exists($field, $request->all())) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle avatar image uploads and deletion
            $avatarFields = ['avatar_1', 'avatar_2', 'avatar_3', 'avatar_4'];
            
            // Collect all files that need validation
            $filesToValidate = [];
            foreach ($avatarFields as $field) {
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

            foreach ($avatarFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($section->$field) {
                        Storage::disk('public')->delete($section->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('reviews-section', 'public');
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
                
                foreach ($textFields as $field) {
                    if (isset($dataToUpdate[$field])) {
                        if ($field === 'call_to_action_text') {
                            $rules[$field] = ['nullable', 'string'];
                        } elseif ($field === 'button_url') {
                            $rules[$field] = ['nullable', 'string', 'max:500'];
                        } else {
                            $rules[$field] = ['nullable', 'string', 'max:255'];
                        }
                    }
                }
                
                foreach ($avatarFields as $field) {
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
                $this->broadcastContentUpdate('reviews-section', 'updated', [
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
                'message' => 'Reviews section updated successfully',
                'data' => [
                    'reviews_section' => [
                        'id' => $section->id,
                        'main_heading' => $section->main_heading,
                        'average_rating' => $section->average_rating,
                        'call_to_action_text' => $section->call_to_action_text,
                        'client_label' => $section->client_label,
                        'review_count' => $section->review_count,
                        'button_text' => $section->button_text,
                        'button_url' => $section->button_url,
                        'avatar_1' => $section->avatar_1_url,
                        'avatar_2' => $section->avatar_2_url,
                        'avatar_3' => $section->avatar_3_url,
                        'avatar_4' => $section->avatar_4_url,
                        'background_color' => $section->background_color,
                        'card_background_color' => $section->card_background_color,
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
                'message' => 'Reviews section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during reviews section update.',
            ], 500);
        }
    }

    /**
     * Delete reviews section content.
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
                    'message' => 'Unauthorized. Only admins can delete reviews section content.',
                ], 403);
            }

            $section = ReviewsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reviews section not found.',
                ], 404);
            }

            // Delete associated avatar images from storage
            $avatarFields = ['avatar_1', 'avatar_2', 'avatar_3', 'avatar_4'];
            foreach ($avatarFields as $field) {
                if ($section->$field) {
                    Storage::disk('public')->delete($section->$field);
                }
            }

            // Delete the section record
            $sectionId = $section->id;
            $section->delete();

            // Broadcast content update
            $this->broadcastContentUpdate('reviews-section', 'deleted', [
                'id' => $sectionId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reviews section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reviews section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during reviews section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from reviews section.
     * 
     * Deletes a single field (e.g., an avatar image) from the section without deleting the entire section.
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
                    'message' => 'Unauthorized. Only admins can delete reviews section fields.',
                ], 403);
            }

            $section = ReviewsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reviews section not found.',
                ], 404);
            }

            // List of all allowed fields
            $allowedFields = [
                'avatar_1',
                'avatar_2',
                'avatar_3',
                'avatar_4',
            ];

            // Validate field name
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name. Allowed fields: ' . implode(', ', $allowedFields),
                ], 422);
            }

            // Delete image file if it's an avatar field
            if (in_array($field, $allowedFields) && $section->$field) {
                Storage::disk('public')->delete($section->$field);
            }

            // Set field to null in database
            $section->$field = null;
            $section->save();
            $section->refresh();

            // Broadcast content update
            $this->broadcastContentUpdate('reviews-section', 'updated', [
                'id' => $section->id,
                'field' => $field,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'reviews_section' => [
                        'id' => $section->id,
                        'main_heading' => $section->main_heading,
                        'average_rating' => $section->average_rating,
                        'call_to_action_text' => $section->call_to_action_text,
                        'client_label' => $section->client_label,
                        'review_count' => $section->review_count,
                        'button_text' => $section->button_text,
                        'button_url' => $section->button_url,
                        'avatar_1' => $section->avatar_1_url,
                        'avatar_2' => $section->avatar_2_url,
                        'avatar_3' => $section->avatar_3_url,
                        'avatar_4' => $section->avatar_4_url,
                        'background_color' => $section->background_color,
                        'card_background_color' => $section->card_background_color,
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
