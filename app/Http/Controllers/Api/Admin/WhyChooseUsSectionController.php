<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhyChooseUsSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WhyChooseUsSectionController extends Controller
{
    /**
     * Get why choose us section content.
     * 
     * Returns the current why choose us section configuration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $section = WhyChooseUsSection::first();

        if (!$section) {
            return response()->json([
                'success' => true,
                'data' => [
                    'why_choose_us_section' => null,
                    'message' => 'Why choose us section not configured yet.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'why_choose_us_section' => [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'background_image' => $section->background_image_url,
                    'background_color' => $section->background_color,
                    'image_1' => $section->image_1_url,
                    'image_2' => $section->image_2_url,
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create or update why choose us section content.
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
                    'message' => 'Unauthorized. Only admins can manage why choose us section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'section_title' => ['nullable', 'string', 'max:255'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_color' => ['nullable', 'string', 'max:50'],
                'image_1' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'image_2' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            ]);

            $existingSection = WhyChooseUsSection::first();

            // Handle section_title
            if (!isset($validated['section_title'])) {
                $validated['section_title'] = $existingSection->section_title ?? null;
            }
            if (!isset($validated['background_color'])) {
                $validated['background_color'] = $existingSection->background_color ?? null;
            }

            // Handle image uploads
            $imageFields = ['background_image', 'image_1', 'image_2'];
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($existingSection && $existingSection->$field) {
                        Storage::disk('public')->delete($existingSection->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('why-choose-us-section', 'public');
                    $validated[$field] = $imagePath;
                } else {
                    // Keep existing image if not updating
                    $validated[$field] = $existingSection->$field ?? null;
                }
            }

            // Update or create section
            $section = WhyChooseUsSection::updateOrCreate(
                ['id' => WhyChooseUsSection::first()?->id ?? 0],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Why choose us section updated successfully',
                'data' => [
                    'why_choose_us_section' => [
                    'id' => $section->id,
                    'background_image' => $section->background_image_url,
                    'background_color' => $section->background_color,
                    'image_1' => $section->image_1_url,
                    'image_2' => $section->image_2_url,
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
                'message' => 'Why choose us section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during why choose us section update.',
            ], 500);
        }
    }

    /**
     * Update why choose us section content.
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
                    'message' => 'Unauthorized. Only admins can manage why choose us section content.',
                ], 403);
            }

            $section = WhyChooseUsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Why choose us section not found.',
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

            // Handle section_title
            if ($request->filled('section_title')) {
                $dataToUpdate['section_title'] = $request->input('section_title');
            } elseif ($request->has('section_title') || array_key_exists('section_title', $request->all())) {
                $dataToUpdate['section_title'] = $request->input('section_title');
            }
            if ($request->filled('background_color')) {
                $dataToUpdate['background_color'] = $request->input('background_color');
            } elseif ($request->has('background_color') || array_key_exists('background_color', $request->all())) {
                $dataToUpdate['background_color'] = $request->input('background_color');
            }

            // Handle image uploads and deletion
            $imageFields = ['background_image', 'image_1', 'image_2'];
            
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
                if ($request->has('background_color')) {
                    $validationRules['background_color'] = ['nullable', 'string', 'max:50'];
                }
                $request->validate($validationRules);
            }
            if ($request->has('background_color')) {
                $request->validate([
                    'background_color' => ['nullable', 'string', 'max:50'],
                ]);
            }

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($section->$field) {
                        Storage::disk('public')->delete($section->$field);
                    }

                    // Store new image
                    $imagePath = $request->file($field)->store('why-choose-us-section', 'public');
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
                
                if (isset($dataToUpdate['section_title'])) {
                    $rules['section_title'] = ['nullable', 'string', 'max:255'];
                }
                if (isset($dataToUpdate['background_image']) && $request->hasFile('background_image')) {
                    $rules['background_image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }
                if (isset($dataToUpdate['image_1']) && $request->hasFile('image_1')) {
                    $rules['image_1'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }
                if (isset($dataToUpdate['image_2']) && $request->hasFile('image_2')) {
                    $rules['image_2'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update
                $section->fill($dataToUpdate);
                $section->save();
                $section->refresh();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Why choose us section updated successfully',
                'data' => [
                    'why_choose_us_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'background_image' => $section->background_image_url,
                        'image_1' => $section->image_1_url,
                        'image_2' => $section->image_2_url,
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
                'message' => 'Why choose us section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during why choose us section update.',
            ], 500);
        }
    }

    /**
     * Delete why choose us section content.
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
                    'message' => 'Unauthorized. Only admins can delete why choose us section content.',
                ], 403);
            }

            $section = WhyChooseUsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Why choose us section not found.',
                ], 404);
            }

            // Delete associated images from storage
            $imageFields = ['background_image', 'image_1', 'image_2'];
            foreach ($imageFields as $field) {
                if ($section->$field) {
                    Storage::disk('public')->delete($section->$field);
                }
            }

            // Delete the section record
            $section->delete();

            return response()->json([
                'success' => true,
                'message' => 'Why choose us section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Why choose us section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during why choose us section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from why choose us section.
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
                    'message' => 'Unauthorized. Only admins can delete why choose us section fields.',
                ], 403);
            }

            $section = WhyChooseUsSection::find($id);

            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Why choose us section not found.',
                ], 404);
            }

            // List of all allowed fields
            $allowedFields = [
                'background_image',
                'image_1',
                'image_2',
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

            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' deleted successfully',
                'data' => [
                    'why_choose_us_section' => [
                        'id' => $section->id,
                        'section_title' => $section->section_title,
                        'background_image' => $section->background_image_url,
                        'image_1' => $section->image_1_url,
                        'image_2' => $section->image_2_url,
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
