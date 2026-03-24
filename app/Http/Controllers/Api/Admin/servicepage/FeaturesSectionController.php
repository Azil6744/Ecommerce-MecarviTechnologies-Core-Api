<?php

namespace App\Http\Controllers\Api\Admin\servicepage;

use App\Http\Controllers\Controller;
use App\Models\FeaturesSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FeaturesSectionController extends Controller
{
    /**
     * Get all features sections.
     * 
     * Returns all features section configurations.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $featuresSections = FeaturesSection::all();

        if ($featuresSections->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'features_sections' => [],
                    'message' => 'Features sections not configured yet.',
                ],
            ], 200);
        }

        $featuresSectionsData = $featuresSections->map(function ($section) {
            return [
                'id' => $section->id,
                'section_title' => $section->section_title,
                'subtitle' => $section->subtitle,
                'title' => $section->title,
                'description' => $section->description,
                'features' => $section->features,
                'button_text' => $section->button_text,
                'main_image' => $section->main_image_url,
                'small_image' => $section->small_image_url,
                'created_at' => $section->created_at,
                'updated_at' => $section->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'features_sections' => $featuresSectionsData,
            ],
        ], 200);
    }

    /**
     * Create features section.
     * 
     * Creates a new features section.
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
                    'message' => 'Unauthorized. Only admins can manage features section content.',
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
                'main_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'small_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            ]);

            // Handle main image upload (only if provided)
            if ($request->hasFile('main_image')) {
                $mainImagePath = $request->file('main_image')->store('features-section', 'public');
                $validated['main_image'] = $mainImagePath;
            }

            // Handle small image upload (only if provided)
            if ($request->hasFile('small_image')) {
                $smallImagePath = $request->file('small_image')->store('features-section', 'public');
                $validated['small_image'] = $smallImagePath;
            }

            // Create new features section
            $featuresSection = FeaturesSection::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Features section created successfully',
                'data' => [
                    'features_section' => [
                        'id' => $featuresSection->id,
                        'section_title' => $featuresSection->section_title,
                        'subtitle' => $featuresSection->subtitle,
                        'title' => $featuresSection->title,
                        'description' => $featuresSection->description,
                        'features' => $featuresSection->features,
                        'button_text' => $featuresSection->button_text,
                        'main_image' => $featuresSection->main_image_url,
                        'small_image' => $featuresSection->small_image_url,
                        'updated_at' => $featuresSection->updated_at,
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
                'message' => 'Features section creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during features section creation.',
            ], 500);
        }
    }

    /**
     * Update features section content.
     * 
     * Updates the existing features section configuration.
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
                    'message' => 'Unauthorized. Only admins can manage features section content.',
                ], 403);
            }

            $featuresSection = FeaturesSection::find($id);

            if (!$featuresSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Features section not found.',
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
                if ($featuresSection->main_image) {
                    Storage::disk('public')->delete($featuresSection->main_image);
                }

                // Store new image
                $mainImagePath = $request->file('main_image')->store('features-section', 'public');
                $dataToUpdate['main_image'] = $mainImagePath;
            }

            // Handle small image upload (only if provided)
            if ($request->hasFile('small_image')) {
                $request->validate([
                    'small_image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                ]);

                // Delete old image if exists
                if ($featuresSection->small_image) {
                    Storage::disk('public')->delete($featuresSection->small_image);
                }

                // Store new image
                $smallImagePath = $request->file('small_image')->store('features-section', 'public');
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

                // Update the features section
                $featuresSection->fill($dataToUpdate);
                $featuresSection->save();
                $featuresSection->refresh();
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
                'message' => 'Features section updated successfully',
                'data' => [
                    'features_section' => [
                        'id' => $featuresSection->id,
                        'section_title' => $featuresSection->section_title,
                        'subtitle' => $featuresSection->subtitle,
                        'title' => $featuresSection->title,
                        'description' => $featuresSection->description,
                        'features' => $featuresSection->features,
                        'button_text' => $featuresSection->button_text,
                        'main_image' => $featuresSection->main_image_url,
                        'small_image' => $featuresSection->small_image_url,
                        'updated_at' => $featuresSection->updated_at,
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
                'message' => 'Features section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during features section update.',
            ], 500);
        }
    }

    /**
     * Show a specific features section by ID.
     * 
     * Returns the features section configuration for a specific ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $featuresSection = FeaturesSection::find($id);

        if (!$featuresSection) {
            return response()->json([
                'success' => false,
                'message' => 'Features section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'features_section' => [
                    'id' => $featuresSection->id,
                    'section_title' => $featuresSection->section_title,
                    'subtitle' => $featuresSection->subtitle,
                    'title' => $featuresSection->title,
                    'description' => $featuresSection->description,
                    'features' => $featuresSection->features,
                    'button_text' => $featuresSection->button_text,
                    'main_image' => $featuresSection->main_image_url,
                    'small_image' => $featuresSection->small_image_url,
                    'created_at' => $featuresSection->created_at,
                    'updated_at' => $featuresSection->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Delete features section content.
     * 
     * Deletes a specific features section record and its associated images.
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
                    'message' => 'Unauthorized. Only admins can delete features section content.',
                ], 403);
            }

            $featuresSection = FeaturesSection::find($id);

            if (!$featuresSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Features section not found.',
                ], 404);
            }

            // Delete associated images from storage
            if ($featuresSection->main_image) {
                Storage::disk('public')->delete($featuresSection->main_image);
            }
            if ($featuresSection->small_image) {
                Storage::disk('public')->delete($featuresSection->small_image);
            }

            // Delete the features section record
            $featuresSection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Features section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Features section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during features section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from features section.
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
                    'message' => 'Unauthorized. Only admins can manage features section content.',
                ], 403);
            }

            $featuresSection = FeaturesSection::find($id);

            if (!$featuresSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Features section not found.',
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
            if ($field === 'main_image' && $featuresSection->main_image) {
                Storage::disk('public')->delete($featuresSection->main_image);
            }
            if ($field === 'small_image' && $featuresSection->small_image) {
                Storage::disk('public')->delete($featuresSection->small_image);
            }

            // Set the field to null
            $featuresSection->$field = null;
            $featuresSection->save();

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully from features section.",
                'data' => [
                    'features_section' => [
                        'id' => $featuresSection->id,
                        'section_title' => $featuresSection->section_title,
                        'subtitle' => $featuresSection->subtitle,
                        'title' => $featuresSection->title,
                        'description' => $featuresSection->description,
                        'features' => $featuresSection->features,
                        'button_text' => $featuresSection->button_text,
                        'main_image' => $featuresSection->main_image_url,
                        'small_image' => $featuresSection->small_image_url,
                        'updated_at' => $featuresSection->updated_at,
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
