<?php

namespace App\Http\Controllers\Api\Admin\servicepage;

use App\Http\Controllers\Controller;
use App\Models\ChartSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ChartSectionController extends Controller
{
    /**
     * Get all chart sections.
     * 
     * Returns all chart section configurations.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $chartSections = ChartSection::all();

        if ($chartSections->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'chart_sections' => [],
                    'message' => 'Chart sections not configured yet.',
                ],
            ], 200);
        }

        $chartSectionsData = $chartSections->map(function ($section) {
            return [
                'id' => $section->id,
                'section_title' => $section->section_title,
                'subtitle' => $section->subtitle,
                'title' => $section->title,
                'description' => $section->description,
                'features' => $section->features,
                'button_text' => $section->button_text,
                'button_url' => $section->button_url,
                'main_image' => $section->main_image_url,
                'small_image' => $section->small_image_url,
                'background_color' => $section->background_color,
                'created_at' => $section->created_at,
                'updated_at' => $section->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'chart_sections' => $chartSectionsData,
            ],
        ], 200);
    }

    /**
     * Create chart section.
     * 
     * Creates a new chart section.
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
                    'message' => 'Unauthorized. Only admins can manage chart section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'section_title' => ['nullable', 'string', 'max:255'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'features' => ['nullable', 'array'],
                'features.*.title' => ['required_with:features', 'string', 'max:255'],
                'features.*.description' => ['required_with:features', 'string'],
                'button_text' => ['nullable', 'string', 'max:255'],
                'button_url' => ['nullable', 'string', 'max:255'],
                'main_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'small_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_color' => ['nullable', 'string', 'max:20'],
            ]);

            // Handle main image upload (only if provided)
            if ($request->hasFile('main_image')) {
                $mainImagePath = $request->file('main_image')->store('chart-section', 'public');
                $validated['main_image'] = $mainImagePath;
            }

            // Handle small image upload (only if provided)
            if ($request->hasFile('small_image')) {
                $smallImagePath = $request->file('small_image')->store('chart-section', 'public');
                $validated['small_image'] = $smallImagePath;
            }

            // Create new chart section
            $chartSection = ChartSection::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Chart section created successfully',
                'data' => [
                    'chart_section' => [
                        'id' => $chartSection->id,
                        'section_title' => $chartSection->section_title,
                        'subtitle' => $chartSection->subtitle,
                        'title' => $chartSection->title,
                        'description' => $chartSection->description,
                        'features' => $chartSection->features,
                        'button_text' => $chartSection->button_text,
                        'button_url' => $chartSection->button_url,
                        'main_image' => $chartSection->main_image_url,
                        'small_image' => $chartSection->small_image_url,
                        'background_color' => $chartSection->background_color,
                        'updated_at' => $chartSection->updated_at,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Chart section creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during chart section creation.',
            ], 500);
        }
    }

    /**
     * Update chart section content.
     * 
     * Updates the existing chart section configuration.
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
                    'message' => 'Unauthorized. Only admins can manage chart section content.',
                ], 403);
            }

            $chartSection = ChartSection::find($id);

            if (!$chartSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chart section not found.',
                ], 404);
            }

            // Get all input data - handle both form-data and JSON
            $dataToUpdate = [];
            
            // For PUT/PATCH with form-data, Laravel may not parse it automatically
            $allInput = $request->all();
            if (empty($allInput) && $request->header('Content-Type') && str_contains($request->header('Content-Type'), 'multipart/form-data')) {
                $allInput = !empty($_POST) ? $_POST : [];
            }
            
            // Merge request data with parsed form-data
            if (!empty($allInput)) {
                $request->merge($allInput);
            }
            
            // Check and update each field
            $fields = [
                'section_title' => 'string|max:255',
                'subtitle' => 'string|max:255',
                'title' => 'string|max:255',
                'description' => 'string',
                'features' => 'array',
                'button_text' => 'string|max:255',
                'button_url' => 'string|max:255',
                'background_color' => 'string|max:20',
            ];

            foreach ($fields as $field => $rules) {
                if ($request->has($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle main image upload (only if provided)
            if ($request->hasFile('main_image')) {
                $request->validate([
                    'main_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                // Delete old image if exists
                if ($chartSection->main_image) {
                    Storage::disk('public')->delete($chartSection->main_image);
                }

                // Store new image
                $mainImagePath = $request->file('main_image')->store('chart-section', 'public');
                $dataToUpdate['main_image'] = $mainImagePath;
            }

            // Handle small image upload (only if provided)
            if ($request->hasFile('small_image')) {
                $request->validate([
                    'small_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                // Delete old image if exists
                if ($chartSection->small_image) {
                    Storage::disk('public')->delete($chartSection->small_image);
                }

                // Store new image
                $smallImagePath = $request->file('small_image')->store('chart-section', 'public');
                $dataToUpdate['small_image'] = $smallImagePath;
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                foreach ($dataToUpdate as $key => $value) {
                    if (isset($fields[$key])) {
                        $rules[$key] = ['nullable', ...explode('|', $fields[$key])];
                    }
                }

                // Special validation for features array
                if (isset($dataToUpdate['features'])) {
                    $rules['features'] = ['nullable', 'array'];
                    $rules['features.*.title'] = ['required_with:features', 'string', 'max:255'];
                    $rules['features.*.description'] = ['required_with:features', 'string'];
                }

                // Validate
                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update the chart section
                $chartSection->fill($dataToUpdate);
                $chartSection->save();
                $chartSection->refresh();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                    'debug' => [
                        'all_input' => $request->all(),
                        'content_type' => $request->header('Content-Type'),
                    ],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chart section updated successfully',
                'data' => [
                    'chart_section' => [
                        'id' => $chartSection->id,
                        'section_title' => $chartSection->section_title,
                        'subtitle' => $chartSection->subtitle,
                        'title' => $chartSection->title,
                        'description' => $chartSection->description,
                        'features' => $chartSection->features,
                        'button_text' => $chartSection->button_text,
                        'button_url' => $chartSection->button_url,
                        'main_image' => $chartSection->main_image_url,
                        'small_image' => $chartSection->small_image_url,
                        'background_color' => $chartSection->background_color,
                        'updated_at' => $chartSection->updated_at,
                    ],
                ],
            ], 200);
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Chart section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during chart section update.',
            ], 500);
        }
    }

    /**
     * Show a specific chart section by ID.
     * 
     * Returns the chart section configuration for a specific ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $chartSection = ChartSection::find($id);

        if (!$chartSection) {
            return response()->json([
                'success' => false,
                'message' => 'Chart section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'chart_section' => [
                    'id' => $chartSection->id,
                    'section_title' => $chartSection->section_title,
                    'subtitle' => $chartSection->subtitle,
                    'title' => $chartSection->title,
                    'description' => $chartSection->description,
                    'features' => $chartSection->features,
                    'button_text' => $chartSection->button_text,
                    'button_url' => $chartSection->button_url,
                    'main_image' => $chartSection->main_image_url,
                    'small_image' => $chartSection->small_image_url,
                    'background_color' => $chartSection->background_color,
                    'created_at' => $chartSection->created_at,
                    'updated_at' => $chartSection->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete chart section content.
     * 
     * Deletes a specific chart section record and its associated images.
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
                    'message' => 'Unauthorized. Only admins can delete chart section content.',
                ], 403);
            }

            $chartSection = ChartSection::find($id);

            if (!$chartSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chart section not found.',
                ], 404);
            }

            // Delete associated images from storage
            if ($chartSection->main_image) {
                Storage::disk('public')->delete($chartSection->main_image);
            }
            if ($chartSection->small_image) {
                Storage::disk('public')->delete($chartSection->small_image);
            }

            // Delete the chart section record
            $chartSection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Chart section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Chart section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during chart section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from chart section.
     * 
     * Sets a specific field to null and removes associated images if applicable.
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
                    'message' => 'Unauthorized. Only admins can manage chart section content.',
                ], 403);
            }

            $chartSection = ChartSection::find($id);

            if (!$chartSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chart section not found.',
                ], 404);
            }

            // Validate field name
            $allowedFields = [
                'section_title',
                'subtitle',
                'title',
                'description',
                'features',
                'button_text',
                'button_url',
                'main_image',
                'small_image'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name.',
                    'allowed_fields' => $allowedFields,
                ], 400);
            }

            // Handle image deletion for image fields
            if ($field === 'main_image' && $chartSection->main_image) {
                Storage::disk('public')->delete($chartSection->main_image);
            }
            if ($field === 'small_image' && $chartSection->small_image) {
                Storage::disk('public')->delete($chartSection->small_image);
            }

            // Set the field to null
            $chartSection->$field = null;
            $chartSection->save();

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully from chart section.",
                'data' => [
                    'chart_section' => [
                        'id' => $chartSection->id,
                        'section_title' => $chartSection->section_title,
                        'subtitle' => $chartSection->subtitle,
                        'title' => $chartSection->title,
                        'description' => $chartSection->description,
                        'features' => $chartSection->features,
                        'button_text' => $chartSection->button_text,
                        'button_url' => $chartSection->button_url,
                        'main_image' => $chartSection->main_image_url,
                        'small_image' => $chartSection->small_image_url,
                        'background_color' => $chartSection->background_color,
                        'updated_at' => $chartSection->updated_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Field deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during field deletion.',
            ], 500);
        }
    }
}
