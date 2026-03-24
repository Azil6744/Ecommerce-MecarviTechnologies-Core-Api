<?php

namespace App\Http\Controllers\Api\Admin\servicepage;

use App\Http\Controllers\Controller;
use App\Models\TabSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TabSectionController extends Controller
{
    /**
     * Get all tab sections.
     * 
     * Returns all tab section configurations with their fixed tabs.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $tabSections = TabSection::all();

            if ($tabSections->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'tab_sections' => [],
                        'message' => 'Tab sections not configured yet.',
                    ],
                ], 200);
            }

            // Debug: Check if tabSections is null
            if ($tabSections === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tab sections query returned null',
                ], 500);
            }

            $tabSectionsData = $tabSections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'section_title' => $section->section_title,
                    'section_description' => $section->section_description,
                    'background_color' => $section->background_color,
                    'tabs' => [
                        [
                            'id' => 1,
                            'tab_title' => $section->tab1_title,
                            'tab_icon' => $section->tab1_icon_url,
                            'tab_content' => $section->tab1_content,
                            'features' => $section->tab1_features,
                            'tab_image' => $section->tab1_image_url,
                            'order' => 0,
                        ],
                        [
                            'id' => 2,
                            'tab_title' => $section->tab2_title,
                            'tab_icon' => $section->tab2_icon_url,
                            'tab_content' => $section->tab2_content,
                            'features' => $section->tab2_features,
                            'tab_image' => $section->tab2_image_url,
                            'order' => 1,
                        ],
                        [
                            'id' => 3,
                            'tab_title' => $section->tab3_title,
                            'tab_icon' => $section->tab3_icon_url,
                            'tab_content' => $section->tab3_content,
                            'features' => $section->tab3_features,
                            'tab_image' => $section->tab3_image_url,
                            'order' => 2,
                        ],
                    ],
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'tab_sections' => $tabSectionsData,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in index method: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Show a specific tab section by ID with its tabs.
     * 
     * Returns the tab section configuration for a specific ID with associated tabs.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $tabSection = TabSection::find($id);

        if (!$tabSection) {
            return response()->json([
                'success' => false,
                'message' => 'Tab section not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tab_section' => [
                    'id' => $tabSection->id,
                    'section_title' => $tabSection->section_title,
                    'section_description' => $tabSection->section_description,
                    'background_color' => $tabSection->background_color,
                    'tabs' => [
                        [
                            'id' => 1,
                            'tab_title' => $tabSection->tab1_title,
                            'tab_icon' => $tabSection->tab1_icon_url,
                            'tab_content' => $tabSection->tab1_content,
                            'features' => $tabSection->tab1_features,
                            'tab_image' => $tabSection->tab1_image_url,
                            'order' => 0,
                        ],
                        [
                            'id' => 2,
                            'tab_title' => $tabSection->tab2_title,
                            'tab_icon' => $tabSection->tab2_icon_url,
                            'tab_content' => $tabSection->tab2_content,
                            'features' => $tabSection->tab2_features,
                            'tab_image' => $tabSection->tab2_image_url,
                            'order' => 1,
                        ],
                        [
                            'id' => 3,
                            'tab_title' => $tabSection->tab3_title,
                            'tab_icon' => $tabSection->tab3_icon_url,
                            'tab_content' => $tabSection->tab3_content,
                            'features' => $tabSection->tab3_features,
                            'tab_image' => $tabSection->tab3_image_url,
                            'order' => 2,
                        ],
                    ],
                    'created_at' => $tabSection->created_at,
                    'updated_at' => $tabSection->updated_at,
                ],
            ],
        ], 200);
    }

    /**
     * Create tab section with fixed tabs.
     * 
     * Creates a new tab section with 3 fixed tabs.
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
                    'message' => 'Unauthorized. Only admins can manage tab section content.',
                ], 403);
            }

            // Validate the incoming request data
            $validated = $request->validate([
                'section_title' => ['nullable', 'string', 'max:255'],
                'section_description' => ['nullable', 'string'],
                'background_color' => ['nullable', 'string', 'max:50'],
                
                // Tab 1 validation
                'tab1_title' => ['nullable', 'string', 'max:255'],
                'tab1_icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'tab1_content' => ['nullable', 'string'],
                'tab1_features' => ['nullable', 'array'],
                'tab1_features.*' => ['string'],
                'tab1_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                
                // Tab 2 validation
                'tab2_title' => ['nullable', 'string', 'max:255'],
                'tab2_icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'tab2_content' => ['nullable', 'string'],
                'tab2_features' => ['nullable', 'array'],
                'tab2_features.*' => ['string'],
                'tab2_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                
                // Tab 3 validation - structured features with title and description
                'tab3_title' => ['nullable', 'string', 'max:255'],
                'tab3_icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'tab3_features' => ['nullable', 'array'],
                'tab3_features.*.title' => ['nullable', 'string'],
                'tab3_features.*.description' => ['nullable', 'string'],
                'tab3_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                
                // Generic image fields for frontend compatibility
                'tab_icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
                'tab_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'],
            ]);

            // Handle tab 1 icon upload
            if ($request->hasFile('tab1_icon')) {
                $validated['tab1_icon'] = $request->file('tab1_icon')->store('tab-section/icons', 'public');
            }

            // Handle tab 1 image upload
            if ($request->hasFile('tab1_image')) {
                $validated['tab1_image'] = $request->file('tab1_image')->store('tab-section/images', 'public');
            }

            // Handle tab 2 icon upload
            if ($request->hasFile('tab2_icon')) {
                $validated['tab2_icon'] = $request->file('tab2_icon')->store('tab-section/icons', 'public');
            }

            // Handle tab 2 image upload
            if ($request->hasFile('tab2_image')) {
                $validated['tab2_image'] = $request->file('tab2_image')->store('tab-section/images', 'public');
            }

            // Handle tab 3 icon upload
            if ($request->hasFile('tab3_icon')) {
                $validated['tab3_icon'] = $request->file('tab3_icon')->store('tab-section/icons', 'public');
            }

            // Handle tab 3 image upload
            if ($request->hasFile('tab3_image')) {
                $validated['tab3_image'] = $request->file('tab3_image')->store('tab-section/images', 'public');
            }

            // Handle generic tab_icon field - map to appropriate tab based on context
            if ($request->hasFile('tab_icon')) {
                // Determine which tab to update based on other fields present
                if ($request->has('tab1_title') || $request->has('tab1_content')) {
                    $validated['tab1_icon'] = $request->file('tab_icon')->store('tab-section/icons', 'public');
                } elseif ($request->has('tab2_title') || $request->has('tab2_content')) {
                    $validated['tab2_icon'] = $request->file('tab_icon')->store('tab-section/icons', 'public');
                } elseif ($request->has('tab3_title')) {
                    $validated['tab3_icon'] = $request->file('tab_icon')->store('tab-section/icons', 'public');
                } else {
                    // Default to tab1_icon if no context
                    $validated['tab1_icon'] = $request->file('tab_icon')->store('tab-section/icons', 'public');
                }
            }

            // Handle generic tab_image field - map to appropriate tab based on context
            if ($request->hasFile('tab_image')) {
                // Determine which tab to update based on other fields present
                if ($request->has('tab1_title') || $request->has('tab1_content')) {
                    $validated['tab1_image'] = $request->file('tab_image')->store('tab-section/images', 'public');
                } elseif ($request->has('tab2_title') || $request->has('tab2_content')) {
                    $validated['tab2_image'] = $request->file('tab_image')->store('tab-section/images', 'public');
                } else {
                    // Default to tab1_image if no context
                    $validated['tab1_image'] = $request->file('tab_image')->store('tab-section/images', 'public');
                }
            }

            // Create new tab section
            $tabSection = TabSection::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tab section created successfully',
                'data' => [
                    'tab_section' => [
                        'id' => $tabSection->id,
                        'section_title' => $tabSection->section_title,
                        'section_description' => $tabSection->section_description,
                        'background_color' => $tabSection->background_color,
                        'tabs' => [
                            [
                                'id' => 1,
                                'tab_title' => $tabSection->tab1_title,
                                'tab_icon' => $tabSection->tab1_icon_url,
                                'tab_content' => $tabSection->tab1_content,
                                'features' => $tabSection->tab1_features,
                                'tab_image' => $tabSection->tab1_image_url,
                                'order' => 0,
                            ],
                            [
                                'id' => 2,
                                'tab_title' => $tabSection->tab2_title,
                                'tab_icon' => $tabSection->tab2_icon_url,
                                'tab_content' => $tabSection->tab2_content,
                                'features' => $tabSection->tab2_features,
                                'tab_image' => $tabSection->tab2_image_url,
                                'order' => 1,
                            ],
                            [
                                'id' => 3,
                                'tab_title' => $tabSection->tab3_title,
                                'tab_icon' => $tabSection->tab3_icon_url,
                                'tab_content' => $tabSection->tab3_content,
                                'features' => $tabSection->tab3_features,
                                'tab_image' => $tabSection->tab3_image_url,
                                'order' => 2,
                            ],
                        ],
                        'updated_at' => $tabSection->updated_at,
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
                'message' => 'Tab section creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during tab section creation.',
            ], 500);
        }
    }

    /**
     * Update tab section content.
     * 
     * Updates the existing tab section configuration and fixed tabs.
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
                    'message' => 'Unauthorized. Only admins can manage tab section content.',
                ], 403);
            }

            $tabSection = TabSection::find($id);

            if (!$tabSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tab section not found.',
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
            
            // Check and update section fields
            $sectionFields = [
                'section_title' => 'string|max:255',
                'section_description' => 'string',
                'background_color' => 'string|max:50',
                'tab1_title' => 'string|max:255',
                'tab1_content' => 'string',
                'tab1_features' => 'array',
                'tab1_features.*' => 'string',
                'tab2_title' => 'string|max:255',
                'tab2_content' => 'string',
                'tab2_features' => 'array',
                'tab2_features.*' => 'string',
                'tab3_title' => 'string|max:255',
                'tab3_content' => 'string',
                'tab3_features' => 'array',
                'tab3_features.*.title' => 'string',
                'tab3_features.*.description' => 'string',
            ];

            foreach ($sectionFields as $field => $rules) {
                if ($request->has($field)) {
                    $dataToUpdate[$field] = $request->input($field);
                }
            }

            // Handle image uploads
            $imageFields = [
                'tab1_icon' => 'tab1_icon',
                'tab1_image' => 'tab1_image',
                'tab2_icon' => 'tab2_icon',
                'tab2_image' => 'tab2_image',
                'tab3_icon' => 'tab3_icon',
                'tab3_image' => 'tab3_image',
            ];

            foreach ($imageFields as $field => $column) {
                if ($request->hasFile($field)) {
                    // Delete old image if exists
                    if ($tabSection->$column) {
                        Storage::disk('public')->delete($tabSection->$column);
                    }
                    
                    // Store new image
                    $dataToUpdate[$column] = $request->file($field)->store('tab-section/' . (str_contains($field, 'icon') ? 'icons' : 'images'), 'public');
                }
            }

            // Handle generic tab_icon field - map to appropriate tab based on context
            if ($request->hasFile('tab_icon')) {
                // Determine which tab to update based on other fields present
                if ($request->has('tab1_title') || $request->has('tab1_content')) {
                    $field = 'tab1_icon';
                    $column = 'tab1_icon';
                } elseif ($request->has('tab2_title') || $request->has('tab2_content')) {
                    $field = 'tab2_icon';
                    $column = 'tab2_icon';
                } elseif ($request->has('tab3_title')) {
                    $field = 'tab3_icon';
                    $column = 'tab3_icon';
                } else {
                    // Default to tab1_icon if no context
                    $field = 'tab1_icon';
                    $column = 'tab1_icon';
                }

                // Delete old image if exists
                if ($tabSection->$column) {
                    Storage::disk('public')->delete($tabSection->$column);
                }
                
                // Store new image
                $dataToUpdate[$column] = $request->file('tab_icon')->store('tab-section/icons', 'public');
            }

            // Handle generic tab_image field - map to appropriate tab based on context
            if ($request->hasFile('tab_image')) {
                // Determine which tab to update based on other fields present
                if ($request->has('tab1_title') || $request->has('tab1_content')) {
                    $field = 'tab1_image';
                    $column = 'tab1_image';
                } elseif ($request->has('tab2_title') || $request->has('tab2_content')) {
                    $field = 'tab2_image';
                    $column = 'tab2_image';
                } else {
                    // Default to tab1_image if no context
                    $field = 'tab1_image';
                    $column = 'tab1_image';
                }

                // Delete old image if exists
                if ($tabSection->$column) {
                    Storage::disk('public')->delete($tabSection->$column);
                }
                
                // Store new image
                $dataToUpdate[$column] = $request->file('tab_image')->store('tab-section/images', 'public');
            }

            // Validate data before updating
            if (!empty($dataToUpdate)) {
                $rules = [];
                
                foreach ($dataToUpdate as $key => $value) {
                    if (isset($sectionFields[$key])) {
                        $rules[$key] = ['nullable', ...explode('|', $sectionFields[$key])];
                    }
                }

                // Add image validation rules for uploaded files
                foreach ($imageFields as $field => $column) {
                    if ($request->hasFile($field)) {
                        $rules[$field] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                    }
                }

                // Add validation for generic image fields
                if ($request->hasFile('tab_icon')) {
                    $rules['tab_icon'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }
                if ($request->hasFile('tab_image')) {
                    $rules['tab_image'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:51200'];
                }

                // Validate
                if (!empty($rules)) {
                    $request->validate($rules);
                }

                // Update the tab section
                $tabSection->fill($dataToUpdate);
                $tabSection->save();
                $tabSection->refresh();
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
                'message' => 'Tab section updated successfully',
                'data' => [
                    'tab_section' => [
                        'id' => $tabSection->id,
                        'section_title' => $tabSection->section_title,
                        'section_description' => $tabSection->section_description,
                        'background_color' => $tabSection->background_color,
                        'tabs' => [
                            [
                                'id' => 1,
                                'tab_title' => $tabSection->tab1_title,
                                'tab_icon' => $tabSection->tab1_icon_url,
                                'tab_content' => $tabSection->tab1_content,
                                'features' => $tabSection->tab1_features,
                                'tab_image' => $tabSection->tab1_image_url,
                                'order' => 0,
                            ],
                            [
                                'id' => 2,
                                'tab_title' => $tabSection->tab2_title,
                                'tab_icon' => $tabSection->tab2_icon_url,
                                'tab_content' => $tabSection->tab2_content,
                                'features' => $tabSection->tab2_features,
                                'tab_image' => $tabSection->tab2_image_url,
                                'order' => 1,
                            ],
                            [
                                'id' => 3,
                                'tab_title' => $tabSection->tab3_title,
                                'tab_icon' => $tabSection->tab3_icon_url,
                                'tab_content' => $tabSection->tab3_content,
                                'features' => $tabSection->tab3_features,
                                'tab_image' => $tabSection->tab3_image_url,
                                'order' => 2,
                            ],
                        ],
                        'updated_at' => $tabSection->updated_at,
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
                'message' => 'Tab section update failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during tab section update.',
            ], 500);
        }
    }

    /**
     * Delete tab section content.
     * 
     * Deletes a specific tab section record and its associated images.
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
                    'message' => 'Unauthorized. Only admins can delete tab section content.',
                ], 403);
            }

            $tabSection = TabSection::find($id);

            if (!$tabSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tab section not found.',
                ], 404);
            }

            // Delete associated images
            $imageFields = [
                'tab1_icon', 'tab1_image',
                'tab2_icon', 'tab2_image',
                'tab3_icon', 'tab3_image'
            ];

            foreach ($imageFields as $field) {
                if ($tabSection->$field) {
                    Storage::disk('public')->delete($tabSection->$field);
                }
            }

            // Delete the tab section record
            $tabSection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tab section deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            // Return general error response
            return response()->json([
                'success' => false,
                'message' => 'Tab section deletion failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during tab section deletion.',
            ], 500);
        }
    }

    /**
     * Delete a specific field from tab section.
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
                    'message' => 'Unauthorized. Only admins can manage tab section content.',
                ], 403);
            }

            $tabSection = TabSection::find($id);

            if (!$tabSection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tab section not found.',
                ], 404);
            }

            // Validate field name
            $allowedFields = [
                'section_title',
                'section_description',
                'background_color',
                'tab1_title',
                'tab1_icon',
                'tab1_content',
                'tab1_features',
                'tab1_image',
                'tab2_title',
                'tab2_icon',
                'tab2_content',
                'tab2_features',
                'tab2_image',
                'tab3_title',
                'tab3_icon',
                'tab3_content',
                'tab3_features',
                'tab3_image',
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field name.',
                    'allowed_fields' => $allowedFields,
                ], 400);
            }

            // Handle image deletion for image fields
            $imageFields = ['tab1_icon', 'tab1_image', 'tab2_icon', 'tab2_image', 'tab3_icon', 'tab3_image'];
            if (in_array($field, $imageFields) && $tabSection->$field) {
                Storage::disk('public')->delete($tabSection->$field);
            }

            // Set the field to null
            $tabSection->$field = null;
            $tabSection->save();

            return response()->json([
                'success' => true,
                'message' => "Field '{$field}' deleted successfully from tab section.",
                'data' => [
                    'tab_section' => [
                    'id' => $tabSection->id,
                    'section_title' => $tabSection->section_title,
                    'section_description' => $tabSection->section_description,
                    'background_color' => $tabSection->background_color,
                    'tabs' => [
                            [
                                'id' => 1,
                                'tab_title' => $tabSection->tab1_title,
                                'tab_icon' => $tabSection->tab1_icon_url,
                                'tab_content' => $tabSection->tab1_content,
                                'features' => $tabSection->tab1_features,
                                'tab_image' => $tabSection->tab1_image_url,
                                'order' => 0,
                            ],
                            [
                                'id' => 2,
                                'tab_title' => $tabSection->tab2_title,
                                'tab_icon' => $tabSection->tab2_icon_url,
                                'tab_content' => $tabSection->tab2_content,
                                'features' => $tabSection->tab2_features,
                                'tab_image' => $tabSection->tab2_image_url,
                                'order' => 1,
                            ],
                            [
                                'id' => 3,
                                'tab_title' => $tabSection->tab3_title,
                                'tab_icon' => $tabSection->tab3_icon_url,
                                'tab_content' => $tabSection->tab3_content,
                                'features' => $tabSection->tab3_features,
                                'tab_image' => $tabSection->tab3_image_url,
                                'order' => 2,
                            ],
                        ],
                        'updated_at' => $tabSection->updated_at,
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
