<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatWeCreateSection;
use App\Traits\BroadcastsContentUpdates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WhatWeCreateSectionController extends Controller
{
    use BroadcastsContentUpdates;
    /**
     * Get what we create section content.
     * 
     * Returns the current what we create section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = WhatWeCreateSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'what_we_create_section' => null,
                    'message' => 'What we create section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'what_we_create_section' => [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'background_image' => $section->background_image_url,
                    'section_bg_color' => $section->section_bg_color,
                    'card_background_color' => $section->card_background_color,
                    'heading_title' => $section->heading_title,
                    'description' => $section->description,
                    'button_text' => $section->button_text,
                    'button_url' => $section->button_url,
                    'grid_background_color' => $section->grid_background_color,
                    'grid_card_background_color' => $section->grid_card_background_color,
                    'grid_background_image' => $section->grid_background_image_url,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update what we create section content.
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
                    'message' => 'Unauthorized. Only admins can manage what we create section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'section_title' => ['nullable', 'string', 'max:255'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'section_bg_color' => ['nullable', 'string', 'max:50'],
                'card_background_color' => ['nullable', 'string', 'max:50'],
                'heading_title' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'button_url' => ['nullable', 'string', 'max:255'],
                'grid_background_color' => ['nullable', 'string', 'max:50'],
                'grid_card_background_color' => ['nullable', 'string', 'max:50'],
                'grid_background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            ]);

            $existingSection = WhatWeCreateSection::first();

            // Handle background image upload (only if provided)
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($existingSection && $existingSection->background_image) {
                    Storage::disk('public')->delete($existingSection->background_image);
                }

                // Store new image
                $imagePath = $request->file('background_image')->store('what-we-create-section', 'public');
                $validated['background_image'] = $imagePath;
            } else {
                // Keep existing image if not updating
                $validated['background_image'] = $existingSection->background_image ?? null;
            }

            // Handle grid background image upload (only if provided)
            if ($request->hasFile('grid_background_image')) {
                // Delete old image if exists
                if ($existingSection && $existingSection->grid_background_image) {
                    Storage::disk('public')->delete($existingSection->grid_background_image);
                }

                // Store new image
                $imagePath = $request->file('grid_background_image')->store('what-we-create-section', 'public');
                $validated['grid_background_image'] = $imagePath;
            } else {
                // Keep existing image if not updating
                $validated['grid_background_image'] = $existingSection->grid_background_image ?? null;
            }

            // Update or create section
            $section = WhatWeCreateSection::updateOrCreate(
                ['id' => WhatWeCreateSection::first()?->id ?? 0],
                $validated
            );

            $this->broadcastContentUpdate('what-we-create-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'What we create section updated successfully',
                'data' => [
                    'what_we_create_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'background_image' => $section->background_image_url,
                        'section_bg_color' => $section->section_bg_color,
                        'card_background_color' => $section->card_background_color,
                        'heading_title' => $section->heading_title,
                        'description' => $section->description,
                        'button_text' => $section->button_text,
                        'button_url' => $section->button_url,
                        'grid_background_color' => $section->grid_background_color,
                        'grid_card_background_color' => $section->grid_card_background_color,
                        'grid_background_image' => $section->grid_background_image_url,
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
                'message' => 'What we create section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during what we create section update.',
            ], 500);
        }
    }

    /**
     * Update what we create section content.
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
                    'message' => 'Unauthorized. Only admins can manage what we create section content.',
                ], 403);
            }

            $section = WhatWeCreateSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'What we create section not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data, Laravel may not parse it automatically
            $isMultipart = $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data');
            
            if ($isMultipart) {
                $allInput = $request->all();
                if (!empty($allInput)) {
                    $request->merge($allInput);
                }
            }

            // Check and update section_title
            if ($request->filled('section_title')) {
                $dataToUpdate['section_title'] = $request->input('section_title');
            } elseif ($request->has('section_title') || array_key_exists('section_title', $request->all())) {
                $dataToUpdate['section_title'] = $request->input('section_title');
            }

            // Check and update section_bg_color
            if ($request->has('section_bg_color') || array_key_exists('section_bg_color', $request->all())) {
                $dataToUpdate['section_bg_color'] = $request->input('section_bg_color');
            }

            // Check and update card_background_color
            if ($request->has('card_background_color') || array_key_exists('card_background_color', $request->all())) {
                $dataToUpdate['card_background_color'] = $request->input('card_background_color');
            }

            // Check and update heading_title
            if ($request->has('heading_title') || array_key_exists('heading_title', $request->all())) {
                $dataToUpdate['heading_title'] = $request->input('heading_title');
            }

            // Check and update description
            if ($request->has('description') || array_key_exists('description', $request->all())) {
                $dataToUpdate['description'] = $request->input('description');
            }

            // Check and update button_text
            if ($request->has('button_text') || array_key_exists('button_text', $request->all())) {
                $dataToUpdate['button_text'] = $request->input('button_text');
            }

            // Check and update button_url
            if ($request->has('button_url') || array_key_exists('button_url', $request->all())) {
                $dataToUpdate['button_url'] = $request->input('button_url');
            }

            // Check and update grid colors
            if ($request->has('grid_background_color') || array_key_exists('grid_background_color', $request->all())) {
                $dataToUpdate['grid_background_color'] = $request->input('grid_background_color');
            }
            if ($request->has('grid_card_background_color') || array_key_exists('grid_card_background_color', $request->all())) {
                $dataToUpdate['grid_card_background_color'] = $request->input('grid_card_background_color');
            }

            // Handle background image upload (only if provided) and deletion
            $fieldValue = $request->input('background_image');
            $fieldExists = $request->has('background_image') || array_key_exists('background_image', $request->all());
            
            if ($request->hasFile('background_image')) {
                // Delete old image if exists
                if ($section->background_image) {
                    Storage::disk('public')->delete($section->background_image);
                }

                // Store new image
                $imagePath = $request->file('background_image')->store('what-we-create-section', 'public');
                $dataToUpdate['background_image'] = $imagePath;
            } elseif ($fieldExists && ($fieldValue === null || $fieldValue === 'delete' || $fieldValue === '')) {
                // Delete old image if exists
                if ($section->background_image) {
                    Storage::disk('public')->delete($section->background_image);
                }
                $dataToUpdate['background_image'] = null;
            }

            // Handle grid background image upload (only if provided) and deletion
            $gridFieldValue = $request->input('grid_background_image');
            $gridFieldExists = $request->has('grid_background_image') || array_key_exists('grid_background_image', $request->all());
            
            if ($request->hasFile('grid_background_image')) {
                if ($section->grid_background_image) {
                    Storage::disk('public')->delete($section->grid_background_image);
                }
                $imagePath = $request->file('grid_background_image')->store('what-we-create-section', 'public');
                $dataToUpdate['grid_background_image'] = $imagePath;
            } elseif ($gridFieldExists && ($gridFieldValue === null || $gridFieldValue === 'delete' || $gridFieldValue === '')) {
                if ($section->grid_background_image) {
                    Storage::disk('public')->delete($section->grid_background_image);
                }
                $dataToUpdate['grid_background_image'] = null;
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                if (isset($dataToUpdate['section_title'])) {
                    $rules['section_title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['background_image']) && $request->hasFile('background_image')) {
                    $rules['background_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }
                if (array_key_exists('section_bg_color', $dataToUpdate)) {
                    $rules['section_bg_color'] = ['nullable', 'string', 'max:50'];
                }
                if (array_key_exists('card_background_color', $dataToUpdate)) {
                    $rules['card_background_color'] = ['nullable', 'string', 'max:50'];
                }
                if (array_key_exists('heading_title', $dataToUpdate)) {
                    $rules['heading_title'] = ['nullable', 'string'];
                }
                if (array_key_exists('description', $dataToUpdate)) {
                    $rules['description'] = ['nullable', 'string'];
                }
                if (array_key_exists('button_text', $dataToUpdate)) {
                    $rules['button_text'] = ['nullable', 'string', 'max:255'];
                }
                if (array_key_exists('button_url', $dataToUpdate)) {
                    $rules['button_url'] = ['nullable', 'string', 'max:255'];
                }
                if (array_key_exists('grid_background_color', $dataToUpdate)) {
                    $rules['grid_background_color'] = ['nullable', 'string', 'max:50'];
                }
                if (array_key_exists('grid_card_background_color', $dataToUpdate)) {
                    $rules['grid_card_background_color'] = ['nullable', 'string', 'max:50'];
                }
                if (isset($dataToUpdate['grid_background_image']) && $request->hasFile('grid_background_image')) {
                    $rules['grid_background_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();

                $this->broadcastContentUpdate('what-we-create-section', 'updated', [
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
                'message' => 'What we create section updated successfully',
                'data' => [
                    'what_we_create_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'background_image' => $section->background_image_url,
                        'section_bg_color' => $section->section_bg_color,
                        'card_background_color' => $section->card_background_color,
                        'heading_title' => $section->heading_title,
                        'description' => $section->description,
                        'button_text' => $section->button_text,
                        'button_url' => $section->button_url,
                        'grid_background_color' => $section->grid_background_color,
                        'grid_card_background_color' => $section->grid_card_background_color,
                        'grid_background_image' => $section->grid_background_image_url,
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
                'message' => 'What we create section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during what we create section update.',
            ], 500);
        }
    }

    /**
     * Delete what we create section content.
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
                    'message' => 'Unauthorized. Only admins can delete what we create section content.',
                ], 403);
            }

            $section = WhatWeCreateSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'What we create section not found.',
                ], 404);
            }

            // Delete associated image from storage
            if ($section->background_image) {
                Storage::disk('public')->delete($section->background_image);
            }
            if ($section->grid_background_image) {
                Storage::disk('public')->delete($section->grid_background_image);
            }

            // Delete the section record
            $section->delete();

            $this->broadcastContentUpdate('what-we-create-section', 'deleted', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'What we create section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'What we create section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during what we create section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from what we create section.
     * 
     * Deletes a single field (e.g., background_image) from the section without deleting the entire section.
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
                    'message' => 'Unauthorized. Only admins can delete what we create section fields.',
                ], 403);
            }

            $section = WhatWeCreateSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'What we create section not found.',
                ], 404);
            }

            // List of all allowed fields
            $allowedFields = [
                'section_title',
                'background_image',
                'section_bg_color',
                'card_background_color',
                'grid_background_color',
                'grid_card_background_color',
                'grid_background_image',
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
            if ($field === 'grid_background_image' && $section->grid_background_image) {
                Storage::disk('public')->delete($section->grid_background_image);
            }

            // Set field to null in database
            $section->$field = null;
            $section->save();
            $section->refresh();

            $this->broadcastContentUpdate('what-we-create-section', 'updated', [
                'id' => $section->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'what_we_create_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'background_image' => $section->background_image_url,
                        'section_bg_color' => $section->section_bg_color,
                        'card_background_color' => $section->card_background_color,
                        'heading_title' => $section->heading_title,
                        'description' => $section->description,
                        'button_text' => $section->button_text,
                        'button_url' => $section->button_url,
                        'grid_background_color' => $section->grid_background_color,
                        'grid_card_background_color' => $section->grid_card_background_color,
                        'grid_background_image' => $section->grid_background_image_url,
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
