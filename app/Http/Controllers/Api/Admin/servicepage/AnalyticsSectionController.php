<?php

namespace App\Http\Controllers\Api\Admin\servicepage;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AnalyticsSectionController extends Controller
{
    /**
     * Get all analytics sections.
     * 
     * Returns all analytics section configurations.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $analyticsSections = AnalyticsSection::all();

        if ($analyticsSections->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'analytics_sections' => [],
                    'message' => 'Analytics sections not configured yet.',
                ],
            ], 200);
        }

        $analyticsSectionsData = $analyticsSections->map(function ($section) {
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
                'background_image' => $section->background_image_url,
                'background_color' => $section->background_color,
                'created_at' => $section->created_at,
                'updated_at' => $section->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'analytics_sections' => $analyticsSectionsData,
            ],
        ], 200);
    }

    /**
     * Create analytics section.
     * 
     * Creates a new analytics section.
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
                    'message' => 'Unauthorized. Only admins can manage analytics section content.',
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
                'button_url' => ['nullable', 'url', 'max:255'],
                'main_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'small_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'background_color' => ['nullable', 'string', 'max:20'],
            ]);

            // Handle main image upload (only if provided)
            if ($request->hasFile('main_image')) {
                $mainImagePath = $request->file('main_image')->store('analytics-section', 'public');
                $validated['main_image'] = $mainImagePath;
            }

            // Handle small image upload (only if provided)
            if ($request->hasFile('small_image')) {
                $smallImagePath = $request->file('small_image')->store('analytics-section', 'public');
                $validated['small_image'] = $smallImagePath;
            }

            // Handle background image upload
            if ($request->hasFile('background_image')) {
                $bgImagePath = $request->file('background_image')->store('analytics-section', 'public');
                $validated['background_image'] = $bgImagePath;
            }

            // Create new analytics section
            $analyticsSection = AnalyticsSection::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Analytics section created successfully',
                'data' => [
                    'analytics_section' => [
                        'id' => $analyticsSection->id,
                        'section_title' => $analyticsSection->section_title,
                        'subtitle' => $analyticsSection->subtitle,
                        'title' => $analyticsSection->title,
                        'description' => $analyticsSection->description,
                        'features' => $analyticsSection->features,
                        'button_text' => $analyticsSection->button_text,
                        'button_url' => $analyticsSection->button_url,
                        'main_image' => $analyticsSection->main_image_url,
                        'small_image' => $analyticsSection->small_image_url,
                        'background_image' => $analyticsSection->background_image_url,
                        'updated_at' => $analyticsSection->updated_at,
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
                'message' => 'Analytics section creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during analytics section creation.',
            ], 500);
        }
    }

    /**
     * Update analytics section content.
     * 
     * Updates the existing analytics section configuration.
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
                    'message' => 'Unauthorized. Only admins can manage analytics section content.',
                ], 403);
            }

            $analyticsSection = AnalyticsSection::find($id);

            if (!$analyticsSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Analytics section not found.',
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
                'button_url' => 'url|max:255',
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
                if ($analyticsSection->main_image) {
                    Storage::disk('public')->delete($analyticsSection->main_image);
                }

                // Store new image
                $mainImagePath = $request->file('main_image')->store('analytics-section', 'public');
                $dataToUpdate['main_image'] = $mainImagePath;
            }

            // Handle small image upload (only if provided)
            if ($request->hasFile('small_image')) {
                $request->validate([
                    'small_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                // Delete old image if exists
                if ($analyticsSection->small_image) {
                    Storage::disk('public')->delete($analyticsSection->small_image);
                }

                // Store new image
                $smallImagePath = $request->file('small_image')->store('analytics-section', 'public');
                $dataToUpdate['small_image'] = $smallImagePath;
            }

            // Handle background image upload (only if provided)
            if ($request->hasFile('background_image')) {
                $request->validate([
                    'background_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                // Delete old image if exists
                if ($analyticsSection->background_image) {
                    Storage::disk('public')->delete($analyticsSection->background_image);
                }

                // Store new image
                $bgImagePath = $request->file('background_image')->store('analytics-section', 'public');
                $dataToUpdate['background_image'] = $bgImagePath;
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

                // Update the analytics section
                $analyticsSection->fill($dataToUpdate);
                $analyticsSection->save();
                $analyticsSection->refresh();
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
                'message' => 'Analytics section updated successfully',
                'data' => [
                    'analytics_section' => [
                        'id' => $analyticsSection->id,
                        'section_title' => $analyticsSection->section_title,
                        'subtitle' => $analyticsSection->subtitle,
                        'title' => $analyticsSection->title,
                        'description' => $analyticsSection->description,
                        'features' => $analyticsSection->features,
                        'button_text' => $analyticsSection->button_text,
                        'button_url' => $analyticsSection->button_url,
                        'main_image' => $analyticsSection->main_image_url,
                        'small_image' => $analyticsSection->small_image_url,
                        'background_image' => $analyticsSection->background_image_url,
                        'background_color' => $analyticsSection->background_color,
                        'updated_at' => $analyticsSection->updated_at,
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
                'message' => 'Analytics section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during analytics section update.',
            ], 500);
        }
    }

    /**
     * Show a specific analytics section by ID.
     * 
     * Returns the analytics section configuration for a specific ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $analyticsSection = AnalyticsSection::find($id);

        if (!$analyticsSection) {
            return response()->json([
                'success' => false,
                'message' => 'Analytics section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'analytics_section' => [
                    'id' => $analyticsSection->id,
                    'section_title' => $analyticsSection->section_title,
                    'subtitle' => $analyticsSection->subtitle,
                    'title' => $analyticsSection->title,
                    'description' => $analyticsSection->description,
                    'features' => $analyticsSection->features,
                    'button_text' => $analyticsSection->button_text,
                    'button_url' => $analyticsSection->button_url,
                    'main_image' => $analyticsSection->main_image_url,
                    'small_image' => $analyticsSection->small_image_url,
                    'background_image' => $analyticsSection->background_image_url,
                    'background_color' => $analyticsSection->background_color,
                    'created_at' => $analyticsSection->created_at,
                    'updated_at' => $analyticsSection->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete analytics section content.
     * 
     * Deletes a specific analytics section record and its associated images.
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
                    'message' => 'Unauthorized. Only admins can delete analytics section content.',
                ], 403);
            }

            $analyticsSection = AnalyticsSection::find($id);

            if (!$analyticsSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Analytics section not found.',
                ], 404);
            }

            // Delete associated images from storage
            if ($analyticsSection->main_image) {
                Storage::disk('public')->delete($analyticsSection->main_image);
            }
            if ($analyticsSection->small_image) {
                Storage::disk('public')->delete($analyticsSection->small_image);
            }
            if ($analyticsSection->background_image) {
                Storage::disk('public')->delete($analyticsSection->background_image);
            }

            // Delete the analytics section record
            $analyticsSection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Analytics section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Analytics section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during analytics section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from analytics section.
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
                    'message' => 'Unauthorized. Only admins can manage analytics section content.',
                ], 403);
            }

            $analyticsSection = AnalyticsSection::find($id);

            if (!$analyticsSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Analytics section not found.',
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
                'small_image',
                'background_image'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name.',
                    'allowed_fields' => $allowedFields,
                ], 400);
            }

            // Handle image deletion for image fields
            if ($field === 'main_image' && $analyticsSection->main_image) {
                Storage::disk('public')->delete($analyticsSection->main_image);
            }
            if ($field === 'small_image' && $analyticsSection->small_image) {
                Storage::disk('public')->delete($analyticsSection->small_image);
            }
            if ($field === 'background_image' && $analyticsSection->background_image) {
                Storage::disk('public')->delete($analyticsSection->background_image);
            }

            // Set the field to null
            $analyticsSection->$field = null;
            $analyticsSection->save();

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully from analytics section.",
                'data' => [
                    'analytics_section' => [
                        'id' => $analyticsSection->id,
                        'section_title' => $analyticsSection->section_title,
                        'subtitle' => $analyticsSection->subtitle,
                        'title' => $analyticsSection->title,
                        'description' => $analyticsSection->description,
                        'features' => $analyticsSection->features,
                        'button_text' => $analyticsSection->button_text,
                        'button_url' => $analyticsSection->button_url,
                        'main_image' => $analyticsSection->main_image_url,
                        'small_image' => $analyticsSection->small_image_url,
                        'background_image' => $analyticsSection->background_image_url,
                        'background_color' => $analyticsSection->background_color,
                        'updated_at' => $analyticsSection->updated_at,
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
